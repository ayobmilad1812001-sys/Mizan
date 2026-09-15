<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case Draft = 'draft';
    case Confirmed = 'confirmed';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Confirmed => 'مؤكَّد',
            self::Received => 'مستلَم',
            self::Cancelled => 'ملغى',
        };
    }
}
