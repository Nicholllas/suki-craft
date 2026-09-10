<?php

namespace App\Enums;

enum OrderStatus: string
{
    case AWAITING_QUOTE = 'awaiting_quote';
    case AWAITING_APPROVAL = 'awaiting_approval';
    case PENDING_PAYMENT = 'pending_payment';
    case AWAITING_VERIFICATION = 'awaiting_verification';
    case PAYMENT_CONFIRMED = 'payment_confirmed';
    case PROCESSING = 'processing';
    case OUT_FOR_DELIVERY = 'out_for_delivery';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return __('storefront.order_status.'.$this->value);

    }
}
