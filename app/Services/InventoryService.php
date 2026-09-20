<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Increase a warehouse's balance of a product and record the movement.
     * Base units only. Must be called with $baseQuantity > 0.
     */
    public function increase(
        Product $product,
        Warehouse $warehouse,
        string $baseQuantity,
        StockMovementType $type,
        Model $reference,
        User $user,
        ?string $reason = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $baseQuantity, $type, $reference, $user, $reason) {
            $stock = $this->lockOrCreateStockRow($warehouse, $product);

            $newQuantity = bcadd($stock->quantity, $baseQuantity, 3);
            $stock->forceFill(['quantity' => $newQuantity])->save();

            return $this->recordMovement($product, $warehouse, $baseQuantity, $newQuantity, $type, $reference, $user, $reason);
        });
    }

    /**
     * Decrease a warehouse's balance of a product and record the movement.
     * Throws InsufficientStockException if the balance would go negative
     * (the database CHECK constraint is the last line of defence behind this).
     */
    public function decrease(
        Product $product,
        Warehouse $warehouse,
        string $baseQuantity,
        StockMovementType $type,
        Model $reference,
        User $user,
        ?string $reason = null,
    ): StockMovement {
        return DB::transaction(function () use ($product, $warehouse, $baseQuantity, $type, $reference, $user, $reason) {
            $stock = $this->lockOrCreateStockRow($warehouse, $product);

            if (bccomp($stock->quantity, $baseQuantity, 3) < 0) {
                throw new InsufficientStockException($product->name, $warehouse->name, $stock->quantity, $baseQuantity);
            }

            $newQuantity = bcsub($stock->quantity, $baseQuantity, 3);
            $stock->forceFill(['quantity' => $newQuantity])->save();

            return $this->recordMovement($product, $warehouse, bcmul($baseQuantity, '-1', 3), $newQuantity, $type, $reference, $user, $reason);
        });
    }

    /**
     * Set a warehouse's balance of a product to a physically counted quantity and
     * record the difference as an adjustment movement. Base units only.
     *
     * The previous quantity is whatever is found under the lock here — never a value
     * carried in from a screen, which may be stale by the time it is submitted.
     * Returns null when the counted quantity already matches the balance: there is
     * no difference to record, and the adjustment line CHECK forbids a zero one.
     *
     * @return array{previous: string, new: string, difference: string, movement: StockMovement}|null
     */
    public function adjustTo(
        Product $product,
        Warehouse $warehouse,
        string $countedBaseQuantity,
        Model $reference,
        User $user,
        string $reason,
    ): ?array {
        return DB::transaction(function () use ($product, $warehouse, $countedBaseQuantity, $reference, $user, $reason) {
            $stock = $this->lockOrCreateStockRow($warehouse, $product);

            $previous = $stock->quantity;
            $difference = bcsub($countedBaseQuantity, $previous, 3);

            if (bccomp($difference, '0', 3) === 0) {
                return null;
            }

            $stock->forceFill(['quantity' => $countedBaseQuantity])->save();

            return [
                'previous' => $previous,
                'new' => $countedBaseQuantity,
                'difference' => $difference,
                'movement' => $this->recordMovement(
                    $product, $warehouse, $difference, $countedBaseQuantity,
                    StockMovementType::Adjustment, $reference, $user, $reason,
                ),
            ];
        });
    }

    /**
     * Lock every warehouse+product balance row an operation is about to touch, in one
     * deterministic order. Without this, a transfer A->B and a transfer B->A running at
     * the same moment would each hold the row the other needs next and deadlock.
     * Rows that do not exist yet are created at zero first, since a missing row
     * cannot be locked.
     *
     * @param  array<int, int>  $warehouseIds
     * @param  array<int, int>  $productIds
     */
    public function lockBalances(array $warehouseIds, array $productIds): void
    {
        // Sort before building the insert: MySQL takes locks in the order the VALUES
        // list is written, so two callers passing the same warehouses in opposite
        // order would deadlock on this statement, before the ordered SELECT below
        // ever runs. Both arrays are sorted so every caller inserts in one order.
        $warehouseIds = array_unique($warehouseIds);
        $productIds = array_unique($productIds);
        sort($warehouseIds);
        sort($productIds);

        $rows = [];

        foreach ($warehouseIds as $warehouseId) {
            foreach ($productIds as $productId) {
                $rows[] = [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        WarehouseStock::query()->insertOrIgnore($rows);

        WarehouseStock::query()
            ->whereIn('warehouse_id', $warehouseIds)
            ->whereIn('product_id', $productIds)
            ->orderBy('warehouse_id')
            ->orderBy('product_id')
            ->lockForUpdate()
            ->get();
    }

    private function lockOrCreateStockRow(Warehouse $warehouse, Product $product): WarehouseStock
    {
        $stock = WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->first();

        if ($stock) {
            return $stock;
        }

        // No row yet for this warehouse+product pair. Create it, then re-select
        // with a lock so a concurrent first-movement on the same pair cannot
        // race past this point with a stale in-memory instance.
        WarehouseStock::query()->insertOrIgnore([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function recordMovement(
        Product $product,
        Warehouse $warehouse,
        string $signedQuantity,
        string $balanceAfter,
        StockMovementType $type,
        Model $reference,
        User $user,
        ?string $reason,
    ): StockMovement {
        $movement = (new StockMovement)->forceFill([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'movement_type' => $type,
            'quantity' => $signedQuantity,
            'balance_after' => $balanceAfter,
            'reference_type' => $reference->getMorphClass(),
            'reference_id' => $reference->getKey(),
            'user_id' => $user->id,
            'reason' => $reason,
        ]);
        $movement->save();

        return $movement;
    }
}
