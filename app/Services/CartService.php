<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private Request $request) {}

    public function addItem(int $productId, ?int $variantId, int $quantity, array $customizations = []): CartItem
    {
        return DB::transaction(function () use ($productId, $variantId, $quantity, $customizations) {
            $product = Product::query()
                ->where('is_active', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->findOrFail($productId);

            $variant = $this->resolveVariant($product, $variantId);
            $cart = $this->findOrCreateCurrentCart();
            $attributes = $this->customizationAttributes($customizations);
            $item = $cart->items()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $variant?->id)
                ->where('card_message', $attributes['card_message'])
                ->where('special_note', $attributes['special_note'])
                ->first();

            if ($item) {
                $item->increment('quantity', $quantity);

                return $item->refresh();
            }

            return $cart->items()->create([
                ...$attributes,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => $quantity,
                'unit_price' => (float) $product->base_price + (float) ($variant?->price_adjustment ?? 0),
            ]);
        });
    }

    public function updateQuantity(int $cartItemId, int $quantity): CartItem
    {
        $item = $this->currentCartItem($cartItemId);
        $item->update(['quantity' => $quantity]);

        return $item->refresh();
    }

    public function removeItem(int $cartItemId): void
    {
        $this->currentCartItem($cartItemId)->delete();
    }

    public function getCurrentCart(): ?Cart
    {
        return Cart::query()
            ->when(
                $this->customerId(),
                fn ($query, $customerId) => $query->where('customer_id', $customerId),
                fn ($query) => $query->where('session_id', $this->sessionId())
            )
            ->first();
    }

    public function getShipmentWeightGrams(): int
    {
        $cart = $this->getCurrentCart();

        if (! $cart) {
            return 0;
        }

        $cart->loadMissing('items.product:id,weight_grams');

        return (int) $cart->items->sum(fn (CartItem $item): int => $item->quantity * ($item->product?->weight_grams ?? 1000));
    }

    public function mergeGuestCartIntoCustomer(int $customerId): void
    {
        DB::transaction(function () use ($customerId) {
            $guestCart = Cart::query()->with('items')->where('session_id', $this->sessionId())->first();

            if (! $guestCart) {
                return;
            }

            $customerCart = Cart::query()->firstOrCreate(['customer_id' => $customerId]);
            $customerCart->load('items');

            foreach ($guestCart->items as $guestItem) {
                $matchingItem = $customerCart->items->first(fn (CartItem $item) => $this->itemsMatch($item, $guestItem));

                if ($matchingItem) {
                    $matchingItem->increment('quantity', $guestItem->quantity);

                    continue;
                }

                $guestItem->cart_id = $customerCart->id;
                $guestItem->save();
                $customerCart->items->push($guestItem);
            }

            $guestCart->delete();
        });
    }

    public function getTotal(): float
    {
        $cart = $this->getCurrentCart();

        if (! $cart) {
            return 0;
        }

        return (float) $cart->items()->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total');
    }

    public function getItemCount(): int
    {
        return (int) ($this->getCurrentCart()?->items()->sum('quantity') ?? 0);
    }

    private function findOrCreateCurrentCart(): Cart
    {
        if ($customerId = $this->customerId()) {
            return Cart::query()->firstOrCreate(['customer_id' => $customerId]);
        }

        return Cart::query()->firstOrCreate(['session_id' => $this->sessionId()]);
    }

    private function resolveVariant(Product $product, ?int $variantId): ?ProductVariant
    {
        $variants = $product->variants()->where('is_active', true);

        if ($variantId) {
            return $variants->findOrFail($variantId);
        }

        if ($variants->exists()) {
            throw ValidationException::withMessages(['variant_id' => 'Pilih varian produk terlebih dahulu.']);
        }

        return null;
    }

    private function currentCartItem(int $cartItemId): CartItem
    {
        $cart = $this->getCurrentCart();

        if (! $cart) {
            throw (new ModelNotFoundException)->setModel(CartItem::class, [$cartItemId]);
        }

        return $cart->items()->findOrFail($cartItemId);
    }

    private function customizationAttributes(array $customizations): array
    {
        return [
            'card_message' => filled($customizations['card_message'] ?? null) ? trim($customizations['card_message']) : null,
            'special_note' => filled($customizations['special_note'] ?? null) ? trim($customizations['special_note']) : null,
        ];
    }

    private function customerId(): ?int
    {
        return $this->request->user('customer')?->id;
    }

    private function itemsMatch(CartItem $first, CartItem $second): bool
    {
        return $first->product_id === $second->product_id
            && $first->product_variant_id === $second->product_variant_id
            && $first->card_message === $second->card_message
            && $first->special_note === $second->special_note;
    }

    private function sessionId(): string
    {
        return $this->request->session()->getId();
    }
}
