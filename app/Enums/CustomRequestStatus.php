<?php

namespace App\Enums;

enum CustomRequestStatus: string
{
    case WAITING_REVIEW = 'waiting_review';
    case QUOTATION_SENT = 'quotation_sent';
    case REVISION_REQUESTED = 'revision_requested';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case CONVERTED_TO_CART = 'converted_to_cart';

    public function label(): string
    {
        return match ($this) {
            self::WAITING_REVIEW => 'Menunggu ditinjau',
            self::QUOTATION_SENT => 'Penawaran dikirim',
            self::REVISION_REQUESTED => 'Revisi diminta',
            self::REJECTED => 'Tidak dapat dipenuhi',
            self::EXPIRED => 'Penawaran kedaluwarsa',
            self::CONVERTED_TO_CART => 'Masuk keranjang',
        };
    }
}
