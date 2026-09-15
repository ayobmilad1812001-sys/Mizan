<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PurchaseReceipt = 'purchase_receipt';
    case Sale = 'sale';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';

    public function label(): string
    {
        return match ($this) {
            self::PurchaseReceipt => 'استلام شراء',
            self::Sale => 'بيع',
            self::Return => 'إرجاع',
            self::Adjustment => 'تسوية',
            self::TransferOut => 'تحويل صادر',
            self::TransferIn => 'تحويل وارد',
        };
    }
}
