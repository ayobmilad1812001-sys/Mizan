<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\DocumentSequence;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseReceiptItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RoundingMode;
use RuntimeException;

class PurchaseService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{product_id:int, product_unit_id:int, quantity:string, unit_cost:string}>  $items
     */
    public function create(array $attributes, array $items, User $user): Purchase
    {
        return DB::transaction(function () use ($attributes, $items, $user) {
            $lines = $this->priceLines($items);
            $subtotal = array_reduce($lines, fn ($carry, $l) => bcadd($carry, $l['line_total'], 3), '0');

            $number = DocumentSequence::next(DocumentType::Purchase, (int) now()->year);

            $purchase = new Purchase;
            $purchase->forceFill([
                'reference_number' => DocumentType::Purchase->format((int) now()->year, $number),
                'supplier_id' => $attributes['supplier_id'],
                'status' => PurchaseStatus::Draft,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'notes' => $attributes['notes'] ?? null,
                'created_by' => $user->id,
            ]);
            $purchase->save();

            foreach ($lines as $line) {
                $item = new PurchaseItem;
                $item->forceFill(['purchase_id' => $purchase->id, ...$line]);
                $item->save();
            }

            return $purchase;
        });
    }

    public function confirm(Purchase $purchase, User $user): Purchase
    {
        if (! $purchase->isDraft()) {
            throw new RuntimeException('لا يمكن تأكيد أمر شراء ليس في حالة مسودة.');
        }

        $purchase->forceFill([
            'status' => PurchaseStatus::Confirmed,
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ])->save();

        return $purchase;
    }

    public function cancel(Purchase $purchase, User $user): Purchase
    {
        if ($purchase->status === PurchaseStatus::Received) {
            throw new RuntimeException('لا يمكن إلغاء أمر شراء مستلَم بالفعل.');
        }
        if ($purchase->status === PurchaseStatus::Cancelled) {
            throw new RuntimeException('أمر الشراء ملغى بالفعل.');
        }

        $purchase->forceFill([
            'status' => PurchaseStatus::Cancelled,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
        ])->save();

        return $purchase;
    }

    /**
     * Full receiving only in V1: every ordered line is received in full, in one
     * operation. purchase_receipts.purchase_id is UNIQUE, so the database itself
     * rejects a second receipt for the same order even under a race condition;
     * the status check below exists to give a clear message before that happens.
     */
    public function receive(Purchase $purchase, Warehouse $warehouse, User $user): PurchaseReceipt
    {
        if (! $purchase->canBeReceived()) {
            throw new RuntimeException('لا يمكن استلام أمر شراء غير مؤكَّد أو مستلَم بالفعل.');
        }

        try {
            return DB::transaction(function () use ($purchase, $warehouse, $user) {
                $receipt = new PurchaseReceipt;
                $receipt->forceFill([
                    'purchase_id' => $purchase->id,
                    'warehouse_id' => $warehouse->id,
                    'received_by' => $user->id,
                ]);
                $receipt->save();

                // Lock rows in a consistent order (by product id) across every line,
                // so two different purchases sharing a product can never deadlock
                // against each other on the warehouse_stocks table.
                $items = $purchase->items()->with('product')->orderBy('product_id')->get();

                foreach ($items as $item) {
                    $receiptItem = new PurchaseReceiptItem;
                    $receiptItem->forceFill([
                        'purchase_receipt_id' => $receipt->id,
                        'purchase_item_id' => $item->id,
                        'quantity' => $item->quantity,
                        'base_quantity' => $item->base_quantity,
                    ]);
                    $receiptItem->save();

                    $this->inventory->increase(
                        product: $item->product,
                        warehouse: $warehouse,
                        baseQuantity: $item->base_quantity,
                        type: StockMovementType::PurchaseReceipt,
                        reference: $receipt,
                        user: $user,
                    );
                }

                $purchase->forceFill(['status' => PurchaseStatus::Received])->save();

                return $receipt;
            });
        } catch (UniqueConstraintViolationException) {
            // Two concurrent "receive" requests for the same order: the loser hits
            // purchase_receipts.purchase_id's UNIQUE constraint. Report it the same
            // way as the pre-check above instead of letting the DB error surface.
            throw new RuntimeException('لا يمكن استلام أمر شراء غير مؤكَّد أو مستلَم بالفعل.');
        }
    }

    /**
     * @param  array<int, array{product_id:int, product_unit_id:int, quantity:string, unit_cost:string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function priceLines(array $items): array
    {
        $units = ProductUnit::whereIn('id', array_column($items, 'product_unit_id'))->get()->keyBy('id');

        return array_map(function ($item) use ($units) {
            $unit = $units[$item['product_unit_id']];
            $baseQuantity = $unit->toBaseQuantity($item['quantity']);
            $lineTotal = bcround(bcmul((string) $item['quantity'], (string) $item['unit_cost'], 6), 3, RoundingMode::HalfAwayFromZero);

            return [
                'product_id' => $item['product_id'],
                'product_unit_id' => $item['product_unit_id'],
                'unit_factor' => $unit->factor,
                'quantity' => $item['quantity'],
                'base_quantity' => $baseQuantity,
                'unit_cost' => $item['unit_cost'],
                'line_total' => $lineTotal,
            ];
        }, $items);
    }
}
