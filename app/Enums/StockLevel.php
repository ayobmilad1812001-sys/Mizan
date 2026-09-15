<?php

namespace App\Enums;

enum StockLevel: string
{
    case Out = 'out';
    case Low = 'low';
    case Available = 'available';

    public function label(): string
    {
        return match ($this) {
            self::Out => 'نفد',
            self::Low => 'قارب الانتهاء',
            self::Available => 'متوفر',
        };
    }
}
