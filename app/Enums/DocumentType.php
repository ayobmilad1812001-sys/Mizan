<?php

namespace App\Enums;

enum DocumentType: string
{
    case Invoice = 'invoice';
    case Purchase = 'purchase';
    case Adjustment = 'adjustment';
    case Transfer = 'transfer';
    case Return = 'return';

    public function defaultPrefix(): string
    {
        return match ($this) {
            self::Invoice => 'INV',
            self::Purchase => 'PO',
            self::Adjustment => 'ADJ',
            self::Transfer => 'TR',
            self::Return => 'RET',
        };
    }

    /**
     * Format a document reference, e.g. INV-2026-00543.
     */
    public function format(int $year, int $number, ?string $prefix = null): string
    {
        return sprintf('%s-%d-%05d', $prefix ?? $this->defaultPrefix(), $year, $number);
    }
}
