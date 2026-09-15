<?php

namespace App\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Disabled = 'disabled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'بانتظار التفعيل',
            self::Active => 'نشط',
            self::Disabled => 'موقوف',
        };
    }
}
