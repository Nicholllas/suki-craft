<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case AWAITING_VERIFICATION = 'awaiting_verification';
    case PAYMENT_CONFIRMED = 'payment_confirmed';
    case PROCESSING = 'processing';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Menunggu pembayaran',
            self::AWAITING_VERIFICATION => 'Menunggu verifikasi',
            self::PAYMENT_CONFIRMED => 'Pembayaran dikonfirmasi',
            self::PROCESSING => 'Sedang dirangkai',
            self::OUT_FOR_DELIVERY => 'Dalam pengiriman',
            self::DELIVERED => 'Telah diterima',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
