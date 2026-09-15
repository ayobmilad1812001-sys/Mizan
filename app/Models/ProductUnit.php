<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'factor', 'barcode', 'purchase_price', 'selling_price'])]
#[Hidden(['base_key'])]
class ProductUnit extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'factor' => 'decimal:4',
            'is_base' => 'boolean',
            'purchase_price' => 'decimal:3',
            'selling_price' => 'decimal:3',
            'status' => ActiveStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Convert a quantity expressed in this unit to base units, without floating point.
     */
    public function toBaseQuantity(string $quantity): string
    {
        return bcmul($quantity, $this->factor, 3);
    }

    public function isActive(): bool
    {
        return $this->status === ActiveStatus::Active;
    }
}
