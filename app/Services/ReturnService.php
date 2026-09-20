<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Enums\SessionStatus;
use App\Enums\StockMovementType;
use App\Models\DocumentSequence;
use App\Models\Refund;
use App\Models\ReturnItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SalesSession;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RoundingMode;
use RuntimeException;

class ReturnService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  array{sale_id:int, reason:string, idempotency_key:string}  $attributes
     * @param  array<int, array{sale_item_id:int, quantity:string}>  $items
     */
    public function create(array $attributes, array $items, User $user): SaleReturn
    {
        if ($existing = SaleReturn::firstWhere('idempotency_key', $attributes['idempotency_key'])) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($attributes, $items, $user) {
                // The refund leaves the drawer of whoever is serving now, so the return
                // belongs to their own open session, not to the session that sold it
                // (which may have closed days ago).
                $session = SalesSession::where('user_id', $user->id)
                    ->where('status', SessionStatus::Open)
                    ->lockForUpdate()
                    ->first();

                if (! $session) {
                    throw new RuntimeException('لا توجد جلسة بيع مفتوحة. افتح جلسة أولاً قبل تنفيذ أي إرجاع.');
                }

                $sale = Sale::with('warehouse')->findOrFail($attributes['sale_id']);

                if (! $sale->isReturnable()) {
                    throw new RuntimeException('الإرجاع مسموح للفواتير النقدية فقط.');
                }

                $lines = $this->measureLines($sale, $items);

                $this->inventory->lockBalances([$sale->warehouse_id], array_column($lines, 'product_id'));

                $total = array_reduce($lines, fn ($carry, $line) => bcadd($carry, $line['line_total'], 3), '0');

                if (bccomp($total, '0', 3) <= 0) {
                    throw new RuntimeException('قيمة المرتجع يجب أن تكون أكبر من صفر.');
                }

                $year = (int) now()->year;
                $number = DocumentSequence::next(DocumentType::Return, $year);

                $return = new SaleReturn;
                $return->forceFill([
                    'reference_number' => DocumentType::Return->format($year, $number),
                    'idempotency_key' => $attributes['idempotency_key'],
                    'sale_id' => $sale->id,
                    // Copied so the composite foreign key can prove, in the database
                    // itself, that the invoice really was a cash sale.
                    'sale_payment_method' => $sale->payment_method,
                    'sales_session_id' => $session->id,
                    'user_id' => $user->id,
                    'reason' => $attributes['reason'],
                    'total_amount' => $total,
                ]);
                $return->save();

                foreach ($lines as $line) {
                    $item = new ReturnItem;
                    $item->forceFill([
                        'return_id' => $return->id,
                        'sale_id' => $sale->id,
                        'sale_item_id' => $line['sale_item_id'],
                        'quantity' => $line['quantity'],
                        'base_quantity' => $line['base_quantity'],
                        'unit_price' => $line['unit_price'],
                        'line_total' => $line['line_total'],
                    ]);
                    $item->save();

                    // The one column a sale line ever allows to change, so a later
                    // return knows how much of this line is still returnable.
                    $line['sale_item']->forceFill([
                        'returned_quantity' => bcadd($line['sale_item']->returned_quantity, $line['quantity'], 3),
                    ])->save();

                    // Goods come back into the warehouse they were sold from.
                    $this->inventory->increase(
                        product: $line['product'],
                        warehouse: $sale->warehouse,
                        baseQuantity: $line['base_quantity'],
                        type: StockMovementType::Return,
                        reference: $return,
                        user: $user,
                        reason: $attributes['reason'],
                    );
                }

                // Cash out of the drawer. The composite foreign key on refunds forces
                // this amount to equal the return total exactly.
                $refund = new Refund;
                $refund->forceFill([
                    'return_id' => $return->id,
                    'amount' => $total,
                    'method' => PaymentMethod::Cash,
                ]);
                $refund->save();

                return $return;
            // Retried on deadlock; see the note in StockTransferService.
            }, 3);
        } catch (UniqueConstraintViolationException) {
            return SaleReturn::where('idempotency_key', $attributes['idempotency_key'])->firstOrFail();
        }
    }

    /**
     * Returns are expressed in the unit that was sold and refunded at the net
     * (post-discount) price, so a piece sold at 1000 under a 20% invoice discount
     * refunds 800, never the pre-discount price.
     *
     * @param  array<int, array{sale_item_id:int, quantity:string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function measureLines(Sale $sale, array $items): array
    {
        // Locked: returned_quantity is a running total, and a second clerk may be
        // refunding the same invoice at this very moment.
        $saleItems = SaleItem::with(['product', 'unit'])
            ->where('sale_id', $sale->id)
            ->whereIn('id', array_column($items, 'sale_item_id'))
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $lines = [];

        foreach ($items as $item) {
            $saleItem = $saleItems[(int) $item['sale_item_id']] ?? null;

            if (! $saleItem) {
                throw new RuntimeException('أحد الأصناف المختارة لا يتبع هذه الفاتورة.');
            }

            $quantity = bcadd($item['quantity'], '0', 3);

            // A zero line just means the cashier left that row untouched.
            if (bccomp($quantity, '0', 3) <= 0) {
                continue;
            }

            // Re-read under the lock, never trusted from the screen: part of this line
            // may have been refunded since the form was opened.
            $remaining = $saleItem->returnableQuantity();

            if (bccomp($quantity, $remaining, 3) > 0) {
                throw new RuntimeException(
                    "الكمية المطلوب إرجاعها من \"{$saleItem->product->name}\" أكبر من المتبقي ({$remaining})."
                );
            }

            $lines[] = [
                'sale_item' => $saleItem,
                'sale_item_id' => $saleItem->id,
                'product' => $saleItem->product,
                'product_id' => $saleItem->product_id,
                'quantity' => $quantity,
                'base_quantity' => $saleItem->unit->toBaseQuantity($quantity),
                'unit_price' => (string) $saleItem->net_unit_price,
                'line_total' => bcround(
                    bcmul($quantity, $saleItem->net_unit_price, 9), 3, RoundingMode::HalfAwayFromZero,
                ),
            ];
        }

        if ($lines === []) {
            throw new RuntimeException('لم تُحدَّد أي كمية للإرجاع.');
        }

        usort($lines, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);

        return $lines;
    }
}
