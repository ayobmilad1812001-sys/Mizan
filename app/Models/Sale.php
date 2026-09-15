<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Models\Concerns\Immutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Fully guarded on purpose: sales are written only by the sale service.
 */
class Sale extends Model
{
    use Immutable;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_method' => PaymentMethod::class,
            'subtotal' => 'decimal:3',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:3',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:3',
            'total' => 'decimal:3',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(SalesSession::class, 'sales_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function movements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function isReturnable(): bool
    {
        return $this->payment_method === PaymentMethod::Cash;
    }
}
