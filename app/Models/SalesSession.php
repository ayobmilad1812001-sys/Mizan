<?php

namespace App\Models;

use App\Enums\SessionStatus;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fully guarded on purpose: sessions are opened and closed only by the session service.
 */
#[Hidden(['active_key'])]
class SalesSession extends Model
{
    public const CREATED_AT = 'opened_at';

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SessionStatus::class,
            'opening_float' => 'decimal:3',
            'expected_cash' => 'decimal:3',
            'actual_cash' => 'decimal:3',
            'difference' => 'decimal:3',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function isOpen(): bool
    {
        return $this->status === SessionStatus::Open;
    }
}
