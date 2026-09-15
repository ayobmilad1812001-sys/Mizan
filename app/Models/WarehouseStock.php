<?php

namespace App\Models;

use App\Enums\StockLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fully guarded on purpose: stock balances change only through inventory services.
 */
class WarehouseStock extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function level(): StockLevel
    {
        if (bccomp($this->quantity, '0', 3) <= 0) {
            return StockLevel::Out;
        }

        if (bccomp($this->quantity, $this->product->reorder_level, 3) <= 0) {
            return StockLevel::Low;
        }

        return StockLevel::Available;
    }
}
