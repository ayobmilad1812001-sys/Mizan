<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Enums\SessionStatus;
use App\Enums\StockMovementType;
use App\Models\DocumentSequence;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalesSession;
use App\Models\Setting;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use RoundingMode;
use RuntimeException;

class SaleService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  array{warehouse_id:int, customer_id?:?int, payment_method:string, discount_percent?:?string, notes?:?string, idempotency_key:string}  $attributes
     * @param  array<int, array{product_unit_id:int, quantity:string}>  $items
     */
    public function create(array $attributes, array $items, User $user): Sale
    {
        if ($existing = Sale::firstWhere('idempotency_key', $attributes['idempotency_key'])) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($attributes, $items, $user) {
                // Resolve and lock the employee's own open session inside the transaction.
                // The composite foreign key enforces that the session belongs to this
                // employee, but nothing in the database stops a sale on a closed one.
                $session = SalesSession::where('user_id', $user->id)
                    ->where('status', SessionStatus::Open)
                    ->lockForUpdate()
                    ->first();

                if (! $session) {
                    throw new RuntimeException('لا توجد جلسة بيع مفتوحة. افتح جلسة أولاً قبل تسجيل أي فاتورة.');
                }

                $warehouse = Warehouse::where('status', 'active')->findOrFail($attributes['warehouse_id']);

                $lines = $this->priceLines($items);
                $this->inventory->lockBalances([$warehouse->id], array_column($lines, 'product_id'));

                $subtotal = array_reduce($lines, fn ($carry, $line) => bcadd($carry, $line['gross'], 3), '0');

                $discountPercent = $attributes['discount_percent'] ?? '0';
                $discountAmount = $this->percentageOf($subtotal, $discountPercent);
                $lines = $this->allocateDiscount($lines, $subtotal, $discountAmount);

                // Frozen on the invoice: changing the setting later must never alter the
                // meaning of an invoice that has already been issued.
                $taxRate = (string) (Setting::where('name', 'tax_rate')->value('value') ?? '0');
                $taxAmount = $this->percentageOf(bcsub($subtotal, $discountAmount, 3), $taxRate);
                $total = bcadd(bcsub($subtotal, $discountAmount, 3), $taxAmount, 3);

                $year = (int) now()->year;
                $number = DocumentSequence::next(DocumentType::Invoice, $year);
                $prefix = (string) (Setting::where('name', 'invoice_prefix')->value('value') ?: 'INV');

                $sale = new Sale;
                $sale->forceFill([
                    'invoice_number' => DocumentType::Invoice->format($year, $number, $prefix),
                    'idempotency_key' => $attributes['idempotency_key'],
                    'sales_session_id' => $session->id,
                    'user_id' => $user->id,
                    'customer_id' => $attributes['customer_id'] ?? null,
                    'warehouse_id' => $warehouse->id,
                    'payment_method' => $attributes['payment_method'],
                    'subtotal' => $subtotal,
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'notes' => $attributes['notes'] ?? null,
                ]);
                $sale->save();

                foreach ($lines as $line) {
                    $item = new SaleItem;
                    $item->forceFill([
                        'sale_id' => $sale->id,
                        'product_id' => $line['product_id'],
                        'product_unit_id' => $line['product_unit_id'],
                        'unit_factor' => $line['unit_factor'],
                        'quantity' => $line['quantity'],
                        'base_quantity' => $line['base_quantity'],
                        'unit_price' => $line['unit_price'],
                        'unit_cost' => $line['unit_cost'],
                        'discount_share' => $line['discount_share'],
                        'line_total' => $line['line_total'],
                        'net_unit_price' => $line['net_unit_price'],
                    ]);
                    $item->save();

                    $this->inventory->decrease(
                        product: $line['product'],
                        warehouse: $warehouse,
                        baseQuantity: $line['base_quantity'],
                        type: StockMovementType::Sale,
                        reference: $sale,
                        user: $user,
                    );
                }

                return $sale;
            // Retried on deadlock; see the note in StockTransferService.
            }, 3);
        } catch (UniqueConstraintViolationException) {
            // The same invoice was submitted twice at once and this one lost the race
            // on the idempotency key: hand back the invoice the winner created.
            return Sale::where('idempotency_key', $attributes['idempotency_key'])->firstOrFail();
        }
    }

    /**
     * Prices are read from the database, never taken from the form: the screen sends
     * only the unit and the quantity. Repeated units are merged into one line, because
     * sale_items carries a unique(sale_id, product_unit_id).
     *
     * @param  array<int, array{product_unit_id:int, quantity:string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function priceLines(array $items): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $unitId = (int) $item['product_unit_id'];
            $quantities[$unitId] = isset($quantities[$unitId])
                ? bcadd($quantities[$unitId], $item['quantity'], 3)
                : bcadd($item['quantity'], '0', 3);
        }

        $units = ProductUnit::with('product.baseUnit')->whereIn('id', array_keys($quantities))->get()->keyBy('id');

        $lines = [];

        foreach ($quantities as $unitId => $quantity) {
            $unit = $units[$unitId];

            $lines[] = [
                'product' => $unit->product,
                'product_id' => $unit->product_id,
                'product_unit_id' => $unit->id,
                'unit_factor' => $unit->factor,
                'quantity' => $quantity,
                'base_quantity' => $unit->toBaseQuantity($quantity),
                'unit_price' => (string) $unit->selling_price,
                // Cost per BASE unit, frozen here so that later price changes never
                // rewrite the profit of invoices already issued.
                'unit_cost' => (string) $unit->product->baseUnit->purchase_price,
                'gross' => $this->round(bcmul($quantity, $unit->selling_price, 9)),
            ];
        }

        // Consistent lock order across every sale, so two tills selling the same two
        // products at once cannot deadlock against each other.
        usort($lines, fn ($a, $b) => $a['product_id'] <=> $b['product_id']);

        return $lines;
    }

    /**
     * Spread the invoice discount over the lines so the shares add up to exactly the
     * invoice discount. Each line takes its proportional share truncated to three
     * decimals, then the leftover millimes go one at a time to the lines with the
     * largest discarded fraction. Rounding each line independently would drift by a
     * millime or two and break the invoice CHECK constraints.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    private function allocateDiscount(array $lines, string $subtotal, string $discountAmount): array
    {
        $noDiscount = bccomp($discountAmount, '0', 3) === 0 || bccomp($subtotal, '0', 3) === 0;
        $remainders = [];
        $assigned = '0';

        foreach ($lines as $index => $line) {
            if ($noDiscount) {
                $lines[$index]['discount_share'] = '0.000';

                continue;
            }

            $exact = bcdiv(bcmul($line['gross'], $discountAmount, 9), $subtotal, 9);
            $truncated = bcadd($exact, '0', 3);

            $lines[$index]['discount_share'] = $truncated;
            $remainders[$index] = bcsub($exact, $truncated, 9);
            $assigned = bcadd($assigned, $truncated, 3);
        }

        if (! $noDiscount) {
            $steps = (int) bcdiv(bcsub($discountAmount, $assigned, 3), '0.001', 0);

            $order = array_keys($remainders);
            usort($order, fn ($a, $b) => bccomp($remainders[$b], $remainders[$a], 9));

            for ($i = 0; $i < $steps; $i++) {
                $index = $order[$i % count($order)];
                $lines[$index]['discount_share'] = bcadd($lines[$index]['discount_share'], '0.001', 3);
            }
        }

        foreach ($lines as $index => $line) {
            $lineTotal = bcsub($line['gross'], $lines[$index]['discount_share'], 3);

            $lines[$index]['line_total'] = $lineTotal;
            $lines[$index]['net_unit_price'] = $this->round(bcdiv($lineTotal, $line['quantity'], 9));
        }

        return $lines;
    }

    /** ROUND(amount × percent / 100, 3), matching MySQL's half-away-from-zero rounding. */
    private function percentageOf(string $amount, string $percent): string
    {
        return $this->round(bcdiv(bcmul($amount, $percent, 9), '100', 9));
    }

    private function round(string $value): string
    {
        return bcround($value, 3, RoundingMode::HalfAwayFromZero);
    }
}
