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
