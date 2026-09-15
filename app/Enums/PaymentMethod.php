<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقدي',
            self::Card => 'بطاقة',
            self::Transfer => 'حوالة',
        };
    }
}
