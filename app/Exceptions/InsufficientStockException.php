<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $productName, string $warehouseName, string $available, string $requested)
    {
        parent::__construct(
            "المخزون غير كافٍ لـ \"{$productName}\" في مخزن \"{$warehouseName}\": "
            ."المتوفر {$available}، المطلوب {$requested}."
        );
    }
}
