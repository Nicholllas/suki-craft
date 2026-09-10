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
        return __('storefront.custom_request_status.'.$this->value);
    }
}
