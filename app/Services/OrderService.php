<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private CartService $cartService) {}

    public function createFromCart(array $checkoutData): Order
    {
        return DB::transaction(function () use ($checkoutData) {
            $cart = $this->cartService->getCurrentCart();

            if (! $cart) {
                throw $this->emptyCartException();
            }

            $cart->load(['items.product', 'items.variant']);

            if ($cart->items->isEmpty()) {
                throw $this->emptyCartException();
            }

            $subtotal = $cart->items->sum(fn (CartItem $item): float => $item->subtotal);
            $deliveryFee = (float) config('delivery.flat_fee', 0);
            $order = Order::query()->create([
                'customer_id' => $cart->customer_id,
                'customer_email' => $checkoutData['customer_email'] ?? null,
                'customer_name' => $checkoutData['customer_name'],
                'customer_phone' => $checkoutData['customer_phone'],
                'delivery_address' => $checkoutData['delivery_address'],
                'delivery_date' => $checkoutData['delivery_date'],
                'delivery_fee' => $deliveryFee,
                'delivery_time_slot' => $checkoutData['delivery_time_slot'],
                'notes' => $checkoutData['notes'] ?? null,
                'order_number' => $this->nextOrderNumber(),
                'public_token' => (string) Str::uuid(),
                'status' => OrderStatus::PENDING_PAYMENT,
                'subtotal' => $subtotal,
                'total' => $subtotal + $deliveryFee,
            ]);

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
