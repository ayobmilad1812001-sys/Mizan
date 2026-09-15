<?php

namespace App\Models;

use App\Models\Concerns\Immutable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fully guarded on purpose: sale lines are written only by the sale service.
 */
#[WithoutTimestamps]
class SaleItem extends Model
{
    use Immutable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_factor' => 'decimal:4',
            'quantity' => 'decimal:3',
            'base_quantity' => 'decimal:3',
            'unit_price' => 'decimal:3',
            'unit_cost' => 'decimal:3',
            'discount_share' => 'decimal:3',
            'line_total' => 'decimal:3',
            'net_unit_price' => 'decimal:3',
            'returned_quantity' => 'decimal:3',
        ];
    }

    /**
     * The return service increments this counter; everything else on a sale line is final.
     *
     * @return list<string>
     */
    public function mutableAfterCreation(): array
    {
        return ['returned_quantity'];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnItem::class);
    }

    public function returnableQuantity(): string
    {
        return bcsub($this->quantity, $this->returned_quantity, 3);
    }
}
