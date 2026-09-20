<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\StockMovementType;
use App\Models\DocumentSequence;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StockTransferService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  array{from_warehouse_id:int, to_warehouse_id:int, notes?:?string, idempotency_key:string}  $attributes
     * @param  array<int, array{product_id:int, product_unit_id:int, quantity:string}>  $items
     */
    public function create(array $attributes, array $items, User $user): StockTransfer
    {
        if ($existing = StockTransfer::firstWhere('idempotency_key', $attributes['idempotency_key'])) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($attributes, $items, $user) {
                $from = Warehouse::findOrFail($attributes['from_warehouse_id']);
                $to = Warehouse::findOrFail($attributes['to_warehouse_id']);

                if ($from->id === $to->id) {
                    throw new RuntimeException('لا يمكن التحويل من المخزن إلى نفسه.');
                }

                $lines = $this->measureLines($items);
                $productIds = array_column($lines, 'product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                // Lock both warehouses' rows up front, in one global order: a transfer
                // running the opposite direction at the same instant then takes the same
                // locks in the same sequence instead of crossing over.
                $this->inventory->lockBalances([$from->id, $to->id], $productIds);

                $number = DocumentSequence::next(DocumentType::Transfer, (int) now()->year);

                $transfer = new StockTransfer;
                $transfer->forceFill([
                    'reference_number' => DocumentType::Transfer->format((int) now()->year, $number),
                    'idempotency_key' => $attributes['idempotency_key'],
                    'from_warehouse_id' => $from->id,
                    'to_warehouse_id' => $to->id,
                    'user_id' => $user->id,
                    'notes' => $attributes['notes'] ?? null,
                ]);
                $transfer->save();

                foreach ($lines as $line) {
                    $item = new StockTransferItem;
                    $item->forceFill(['stock_transfer_id' => $transfer->id, ...$line]);
                    $item->save();

                    $this->inventory->decrease(
                        product: $products[$line['product_id']],
                        warehouse: $from,
                        baseQuantity: $line['base_quantity'],
                        type: StockMovementType::TransferOut,
                        reference: $transfer,
                        user: $user,
                    );

                    $this->inventory->increase(
                        product: $products[$line['product_id']],
                        warehouse: $to,
                        baseQuantity: $line['base_quantity'],
                        type: StockMovementType::TransferIn,
                        reference: $transfer,
                        user: $user,
                    );
                }

                return $transfer;
            // Retried on deadlock: MySQL rolls the loser back whole, so replaying the
            // closure is safe, and no lock ordering can fully rule out a deadlock
            // between the INSERT IGNORE that creates missing balance rows and a
            // concurrent SELECT ... FOR UPDATE over the same rows.
            }, 3);
        } catch (UniqueConstraintViolationException) {
            return StockTransfer::where('idempotency_key', $attributes['idempotency_key'])->firstOrFail();
        }
    }

    /**
     * @param  array<int, array{product_id:int, product_unit_id:int, quantity:string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function measureLines(array $items): array
    {
        $units = ProductUnit::whereIn('id', array_column($items, 'product_unit_id'))->get()->keyBy('id');

        $lines = array_map(function (array $item) use ($units) {
            $unit = $units[$item['product_unit_id']];

            return [
                'product_id' => (int) $item['product_id'],
                'product_unit_id' => $unit->id,
                'unit_factor' => $unit->factor,
                'quantity' => $item['quantity'],
                'base_quantity' => $unit->toBaseQuantity($item['quantity']),
            ];
        }, $items);

        usort($lines, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);

        return $lines;
    }
}
