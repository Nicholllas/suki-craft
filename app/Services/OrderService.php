<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\CartItemGroup;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cartService, private PromotionService $promotionService) {}

    public function createFromCart(array $checkoutData, ?string $promotionCode = null): Order
    {
        return DB::transaction(function () use ($checkoutData, $promotionCode) {
            $cart = $this->cartService->getCurrentCart();

            if (! $cart) {
                throw $this->emptyCartException();
            }

            $cart->load(['itemGroups.product', 'itemGroups.variants.productVariant']);

            if ($cart->itemGroups->isEmpty()) {
                throw $this->emptyCartException();
            }

            $subtotal = $cart->itemGroups->sum(fn (CartItemGroup $group): float => $group->subtotal);
            $deliveryFee = (float) config('delivery.flat_fee', 0);
            $promotion = filled($promotionCode) ? $this->promotionService->validate($promotionCode, $subtotal, $checkoutData['customer_phone'], auth('customer')->id()) : null;
            $discountAmount = $promotion ? $this->promotionService->calculateDiscount($promotion, $subtotal) : 0;
            $order = Order::query()->create([
                'customer_id' => auth('customer')->id(),
                'customer_email' => $checkoutData['customer_email'] ?? null,
                'customer_name' => $checkoutData['customer_name'],
                'customer_phone' => $checkoutData['customer_phone'],
                'delivery_address' => $checkoutData['delivery_address'],
                'delivery_date' => $checkoutData['delivery_date'],
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'delivery_time_slot' => $checkoutData['delivery_time_slot'],
                'notes' => $checkoutData['notes'] ?? null,
                'order_number' => $this->nextOrderNumber(),
                'public_token' => (string) Str::uuid(),
                'status' => OrderStatus::PENDING_PAYMENT,
                'subtotal' => $subtotal,
                'total' => $subtotal + $deliveryFee - $discountAmount,
            ]);

            if ($promotion) {
                $this->promotionService->applyToOrder($order, $promotion, $discountAmount);
            }

            foreach ($cart->itemGroups as $cartItemGroup) {
                $orderItemGroup = $order->itemGroups()->create([
                    'bundle_quantity' => $cartItemGroup->bundle_quantity,
                    'card_message' => $cartItemGroup->card_message,
                    'product_id' => $cartItemGroup->product_id,
                    'product_name' => $cartItemGroup->product->name,
                    'special_note' => $cartItemGroup->special_note,
                    'subtotal' => $cartItemGroup->subtotal,
                ]);

                $orderItemGroup->variants()->createMany($cartItemGroup->variants->map(fn ($variant): array => [
                    'line_subtotal' => $variant->lineSubtotal * $cartItemGroup->bundle_quantity,
                    'product_variant_id' => $variant->product_variant_id,
                    'quantity_in_bundle' => $variant->quantity_in_bundle,
                    'unit_price' => $variant->unit_price,
                    'variant_label' => $variant->productVariant->label,
                ])->all());
            }

            $order->statusHistories()->create([
                'note' => 'Pesanan dibuat dan menunggu pembayaran.',
                'status' => OrderStatus::PENDING_PAYMENT,
            ]);

            $cart->itemGroups()->delete();

            return $order;
        });
    }

    public function overrideStatus(Order $order, string $newStatus, Admin $admin, string $reason): void
    {
        $status = OrderStatus::tryFrom($newStatus);

        if (! $status) {
            throw ValidationException::withMessages(['status' => 'Status pesanan yang dipilih tidak valid.']);
        }

        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => 'Catatan atau alasan perubahan status wajib diisi.']);
        }

        DB::transaction(function () use ($admin, $order, $reason, $status): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $previousStatus = $lockedOrder->status;

            $lockedOrder->update(['status' => $status]);
            $lockedOrder->statusHistories()->create([
                'changed_by' => $admin->id,
                'note' => 'Status diubah manual dari '.$previousStatus->label().' menjadi '.$status->label().'. Alasan: '.$reason,
                'status' => $status,
            ]);
        });
    }

    private function emptyCartException(): ValidationException
    {
        return ValidationException::withMessages(['cart' => 'Keranjang belanja Anda masih kosong.']);
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'SC-'.now()->format('Ymd');
        $sequence = Order::query()->where('order_number', 'like', $prefix.'-%')->lockForUpdate()->count() + 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }
}
