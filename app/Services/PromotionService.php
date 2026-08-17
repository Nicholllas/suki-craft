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
            throw $this->exception('Kode promo tidak ditemukan.');
        }
        if (! $promotion->is_active) {
            throw $this->exception('Kode promo sedang tidak aktif.');
        }
        if ($promotion->starts_at->isFuture()) {
            throw $this->exception('Kode promo belum dapat digunakan.');
        }
        if ($promotion->expires_at->isPast()) {
            throw $this->exception('Kode promo sudah kedaluwarsa.');
        }
        if ($promotion->min_purchase !== null && $subtotal < (float) $promotion->min_purchase) {
            throw $this->exception('Minimum pembelian untuk kode promo ini belum tercapai.');
        }
        if ($promotion->usage_limit !== null && $promotion->usages()->count() >= $promotion->usage_limit) {
            throw $this->exception('Kuota penggunaan kode promo ini sudah habis.');
        }
        if ($promotion->usage_limit_per_customer !== null && $this->usageCountForCustomer($promotion, $customerPhone, $customerId) >= $promotion->usage_limit_per_customer) {
            throw $this->exception('Kode promo ini sudah pernah digunakan.');
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

    public function applyToOrder(Order $order, Promotion $promotion, float $discountAmount): void
    {
        $order->update(['promotion_id' => $promotion->id, 'discount_amount' => $discountAmount]);
        $promotion->usages()->create(['order_id' => $order->id, 'customer_id' => $order->customer_id, 'customer_phone' => $this->normalizePhone($order->customer_phone)]);
    }

    private function usageCountForCustomer(Promotion $promotion, ?string $customerPhone, ?int $customerId): int
    {
        $phone = $this->normalizePhone($customerPhone);
        if (! $customerId && ! $phone) {
            throw $this->exception('Masukkan nomor WhatsApp untuk menggunakan kode promo.');
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

    private function normalizePhone(?string $phone): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);

        return str_starts_with($normalized, '62') ? '0'.substr($normalized, 2) : $normalized;
    }

    private function exception(string $message): ValidationException
    {
        return ValidationException::withMessages(['promotion_code' => $message]);
    }
}
