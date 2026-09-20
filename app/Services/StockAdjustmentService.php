<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentSequence;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockAdjustmentService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * Record a physical stock count. Lines whose counted quantity already matches the
     * balance are skipped: they carry no information and the adjustment line CHECK
     * forbids a zero difference.
     *
     * @param  array{warehouse_id:int, reason:string, idempotency_key:string}  $attributes
     * @param  array<int, array{product_id:int, counted_quantity:string}>  $items
     */
    public function create(array $attributes, array $items, User $user): StockAdjustment
    {
        if ($existing = StockAdjustment::firstWhere('idempotency_key', $attributes['idempotency_key'])) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($attributes, $items, $user) {
                $warehouse = Warehouse::findOrFail($attributes['warehouse_id']);

                // Consistent lock order across every adjustment, so two counts running
                // in the same warehouse cannot deadlock against each other.
                usort($items, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);

                $productIds = array_map(fn ($item) => (int) $item['product_id'], $items);
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                $this->inventory->lockBalances([$warehouse->id], $productIds);

                $number = DocumentSequence::next(DocumentType::Adjustment, (int) now()->year);

                $adjustment = new StockAdjustment;
                $adjustment->forceFill([
                    'reference_number' => DocumentType::Adjustment->format((int) now()->year, $number),
                    'idempotency_key' => $attributes['idempotency_key'],
                    'warehouse_id' => $warehouse->id,
                    'user_id' => $user->id,
                    'reason' => $attributes['reason'],
                ]);
                $adjustment->save();

                $recorded = 0;

                foreach ($items as $item) {
                    $result = $this->inventory->adjustTo(
                        product: $products[(int) $item['product_id']],
                        warehouse: $warehouse,
                        countedBaseQuantity: $item['counted_quantity'],
                        reference: $adjustment,
                        user: $user,
                        reason: $attributes['reason'],
                    );

                    if ($result === null) {
                        continue;
                    }

                    $line = new StockAdjustmentItem;
                    $line->forceFill([
                        'stock_adjustment_id' => $adjustment->id,
                        'product_id' => (int) $item['product_id'],
                        'previous_quantity' => $result['previous'],
                        'new_quantity' => $result['new'],
                        'difference' => $result['difference'],
                    ]);
                    $line->save();

                    $recorded++;
                }

                if ($recorded === 0) {
                    throw new RuntimeException('كل الكميات المُدخلة مطابقة للرصيد الحالي، لا توجد تسوية لتسجيلها.');
                }

                return $adjustment;
            // Retried on deadlock; see the note in StockTransferService.
            }, 3);
        } catch (UniqueConstraintViolationException) {
            // The same form was submitted twice at once and this one lost the race on
            // the idempotency key: hand back the adjustment the winner created.
            return StockAdjustment::where('idempotency_key', $attributes['idempotency_key'])->firstOrFail();
        }
    }
}
