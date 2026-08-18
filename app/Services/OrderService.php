<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cartService, private PromotionService $promotionService) {}

    public function createFromCart(array $checkoutData, ?string $promotionCode = null, ?array $shippingSelection = null): Order
    {
        return DB::transaction(function () use ($checkoutData, $promotionCode, $shippingSelection) {
            $cart = $this->cartService->getCurrentCart();

            if (! $cart) {
                throw $this->emptyCartException();
            }

            $cart->load(['items.product', 'items.variant']);

            if ($cart->items->isEmpty()) {
                throw $this->emptyCartException();
            }

            $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->subtotal);
            $shippingSelection = $this->validatedShippingSelection($shippingSelection);
            $deliveryFee = (float) $shippingSelection['delivery_fee'];
            $promotion = filled($promotionCode) ? $this->promotionService->validate($promotionCode, $subtotal, $checkoutData['customer_phone'], auth('customer')->id()) : null;
            $discountAmount = $promotion ? $this->promotionService->calculateDiscount($promotion, $subtotal) : 0;
            $order = Order::query()->create([
                'customer_id' => auth('customer')->id(),
                'customer_email' => $checkoutData['customer_email'] ?? null,
                'customer_name' => $checkoutData['customer_name'],
                'customer_phone' => $checkoutData['customer_phone'],
                'delivery_address' => $checkoutData['delivery_address'],
                'destination_area_id' => $shippingSelection['destination_area_id'],
                'destination_postal_code' => $shippingSelection['destination_postal_code'],
                'delivery_date' => $checkoutData['delivery_date'],
                'delivery_fee' => $deliveryFee,
                'discount_amount' => $discountAmount,
                'delivery_time_slot' => $checkoutData['delivery_time_slot'],
                'courier_company' => $shippingSelection['courier_company'],
                'courier_service' => $shippingSelection['courier_service'],
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

            $order->items()->createMany($cart->items->map(fn (CartItem $item): array => [
                'card_message' => $item->card_message,
                'product_id' => $item->product_id,
                'product_name' => $item->product->name,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'special_note' => $item->special_note,
                'subtotal' => $item->subtotal,
                'unit_price' => $item->unit_price,
                'variant_label' => $item->variant?->label,
            ])->all());

            $order->statusHistories()->create([
                'note' => 'Pesanan dibuat dan menunggu pembayaran.',
                'status' => OrderStatus::PENDING_PAYMENT,
            ]);

            $cart->items()->delete();

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

    private function validatedShippingSelection(?array $shippingSelection): array
    {
        $courierCompany = $shippingSelection['courier_company'] ?? null;
        $courierService = $shippingSelection['courier_service'] ?? null;
        $deliveryFee = $shippingSelection['delivery_fee'] ?? null;

        if (! is_array($shippingSelection) || ! is_numeric($deliveryFee) || blank($courierCompany) || blank($courierService)) {
            throw ValidationException::withMessages(['shipping' => 'Pilih layanan pengiriman sebelum membuat pesanan.']);
        }

        $validCourier = $courierCompany === 'flat_rate' || in_array($courierCompany, config('biteship.couriers', []), true);

        if (! $validCourier || (float) $deliveryFee < 0) {
            throw ValidationException::withMessages(['shipping' => 'Pilihan layanan pengiriman tidak valid. Silakan cek ongkir kembali.']);
        }

        return [
            'courier_company' => $courierCompany,
            'courier_service' => $courierService,
            'delivery_fee' => (float) $deliveryFee,
            'destination_area_id' => $shippingSelection['destination_area_id'] ?? null,
            'destination_postal_code' => $shippingSelection['destination_postal_code'] ?? null,
        ];
    }

    private function nextOrderNumber(): string
    {
        $prefix = 'SC-'.now()->format('Ymd');
        $sequence = Order::query()->where('order_number', 'like', $prefix.'-%')->lockForUpdate()->count() + 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }
}
