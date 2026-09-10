<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use Illuminate\Validation\ValidationException;

class PromotionService
{
    public function validate(string $code, float $subtotal, ?string $customerPhone, ?int $customerId): Promotion
    {
        $promotion = Promotion::query()->where('code', strtoupper(trim($code)))->lockForUpdate()->first();

        if (! $promotion) {
            throw $this->exception('promo_not_found');
        }
        if (! $promotion->is_active) {
            throw $this->exception('promo_inactive');
        }
        if ($promotion->starts_at->isFuture()) {
            throw $this->exception('promo_not_started');
        }
        if ($promotion->expires_at->isPast()) {
            throw $this->exception('promo_expired');
        }
        if ($promotion->min_purchase !== null && $subtotal < (float) $promotion->min_purchase) {
            throw $this->exception('promo_minimum');
        }
        if ($promotion->usage_limit !== null && $promotion->usages()->count() >= $promotion->usage_limit) {
            throw $this->exception('promo_usage_exhausted');
        }
        if ($promotion->usage_limit_per_customer !== null && $this->usageCountForCustomer($promotion, $customerPhone, $customerId) >= $promotion->usage_limit_per_customer) {
            throw $this->exception('promo_already_used');
        }

        return $promotion;
    }

    public function calculateDiscount(Promotion $promotion, float $subtotal): float
    {
        if ($promotion->type === 'fixed') {
            return min((float) $promotion->value, $subtotal);
        }

        $discount = $subtotal * ((float) $promotion->value / 100);
        if ($promotion->max_discount !== null) {
            $discount = min($discount, (float) $promotion->max_discount);
        }

        return min(round($discount, 2), $subtotal);
    }

    public function checkoutPricing(?Promotion $promotion, float $subtotal, float $deliveryFee): array
    {
        $discountAmount = $promotion ? $this->calculateDiscount($promotion, $subtotal) : 0;
        $totalBeforeDiscount = $subtotal + $deliveryFee;

        return [
            'code' => $promotion?->code,
            'discount_amount' => $discountAmount,
            'promotion_type' => $promotion?->type,
            'promotion_value' => $promotion ? (float) $promotion->value : null,
            'total' => $totalBeforeDiscount - $discountAmount,
            'total_before_discount' => $totalBeforeDiscount,
        ];
    }

    public function applyToOrder(Order $order, Promotion $promotion, float $discountAmount): void
    {
        $order->update(['promotion_id' => $promotion->id, 'discount_amount' => $discountAmount]);
        $promotion->usages()->create(['order_id' => $order->id, 'customer_id' => $order->customer_id, 'customer_phone' => PhoneNumberNormalizer::normalize($order->customer_phone)]);
    }

    private function usageCountForCustomer(Promotion $promotion, ?string $customerPhone, ?int $customerId): int
    {
        $phone = filled($customerPhone) ? PhoneNumberNormalizer::normalize($customerPhone) : null;
        if (! $customerId && ! $phone) {
            throw $this->exception('promo_phone_required');
        }

        return PromotionUsage::query()->whereBelongsTo($promotion)->where(function ($query) use ($customerId, $phone): void {
            if ($customerId) {
                $query->where('customer_id', $customerId);
            }
            if ($phone) {
                $query->{$customerId ? 'orWhere' : 'where'}('customer_phone', $phone);
            }
        })->count();
    }

    private function exception(string $messageKey): ValidationException
    {
        return ValidationException::withMessages(['promotion_code' => __("store.validation.messages.{$messageKey}")]);
    }
}
