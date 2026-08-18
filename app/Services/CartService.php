<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItemGroup;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private Request $request) {}

    public function addToCart(Product $product, array $selectedVariants, int $bundleQuantity, array $customizations = []): CartItemGroup
    {
        if ($bundleQuantity < 1 || $bundleQuantity > 99) {
            throw ValidationException::withMessages(['bundle_quantity' => ['Jumlah buket harus antara 1 dan 99.']]);
        }

        $selectedVariants = collect($selectedVariants)->mapWithKeys(fn ($quantity, $variantId): array => [(int) $variantId => (int) $quantity])->all();

        return DB::transaction(function () use ($bundleQuantity, $customizations, $product, $selectedVariants): CartItemGroup {
            $product = Product::query()->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->findOrFail($product->id);
            $variants = $this->resolveVariants($product, $selectedVariants);
            $cart = $this->findOrCreateCurrentCart();
            $attributes = $this->customizationAttributes($customizations);
            $group = $cart->itemGroups()->create([
                ...$attributes,
                'product_id' => $product->id,
                'bundle_quantity' => $bundleQuantity,
            ]);

            $group->variants()->createMany($variants->map(fn (ProductVariant $variant): array => [
                'product_variant_id' => $variant->id,
                'quantity_in_bundle' => $selectedVariants[$variant->id],
                'unit_price' => $variant->price_adjustment,
            ])->all());

            return $group->load(['product', 'variants.productVariant']);
        });
    }

    public function updateQuantity(int $cartItemGroupId, int $quantity): CartItemGroup
    {
        $group = $this->currentCartItemGroup($cartItemGroupId);
        $group->update(['bundle_quantity' => $quantity]);

        return $group->refresh()->load(['product', 'variants.productVariant']);
    }

    public function removeItem(int $cartItemGroupId): void
    {
        $this->currentCartItemGroup($cartItemGroupId)->delete();
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

    public function mergeGuestCartIntoCustomer(int $customerId): void
    {
        DB::transaction(function () use ($customerId) {
            $guestCart = Cart::query()->with('itemGroups.variants')->where('session_id', $this->sessionId())->first();

            if (! $guestCart) {
                return;
            }

            $customerCart = Cart::query()->firstOrCreate(['customer_id' => $customerId]);
            $customerCart->load('itemGroups.variants');

            foreach ($guestCart->itemGroups as $guestGroup) {
                $matchingGroup = $customerCart->itemGroups->first(fn (CartItemGroup $group) => $this->groupsMatch($group, $guestGroup));

                if ($matchingGroup) {
                    $matchingGroup->increment('bundle_quantity', $guestGroup->bundle_quantity);

                    continue;
                }

                $guestGroup->cart_id = $customerCart->id;
                $guestGroup->save();
                $customerCart->itemGroups->push($guestGroup);
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

        return $cart->loadMissing(['itemGroups.product', 'itemGroups.variants'])->itemGroups->sum(fn (CartItemGroup $group): float => $group->subtotal);
    }

    public function getItemCount(): int
    {
        return (int) ($this->getCurrentCart()?->itemGroups()->sum('bundle_quantity') ?? 0);
    }

    private function findOrCreateCurrentCart(): Cart
    {
        if ($customerId = $this->customerId()) {
            return Cart::query()->firstOrCreate(['customer_id' => $customerId]);
        }

        return Cart::query()->firstOrCreate(['session_id' => $this->sessionId()]);
    }

    private function resolveVariants(Product $product, array $selectedVariants): Collection
    {
        $selectedVariants = collect($selectedVariants)->mapWithKeys(fn ($quantity, $variantId): array => [(int) $variantId => (int) $quantity]);

        if (! $product->allow_multiple_variants && $selectedVariants->count() > 1) {
            throw ValidationException::withMessages(['selected_variants' => ['Produk ini hanya dapat menggunakan satu varian.']]);
        }

        $variants = $product->variants()->where('is_active', true)->whereIn('id', $selectedVariants->keys())->get();

        if ($variants->count() !== $selectedVariants->count()) {
            throw ValidationException::withMessages(['selected_variants' => ['Salah satu varian yang dipilih tidak tersedia.']]);
        }

        if ($product->variants()->where('is_active', true)->exists() && $variants->isEmpty()) {
            throw ValidationException::withMessages(['selected_variants' => ['Pilih varian produk terlebih dahulu.']]);
        }

        foreach ($variants as $variant) {
            if ($variant->is_quantity_based && $selectedVariants[$variant->id] <= 0) {
                throw ValidationException::withMessages(['selected_variants' => ['Jumlah varian harus lebih dari nol.']]);
            }

            if (! $variant->is_quantity_based && $selectedVariants[$variant->id] !== 1) {
                throw ValidationException::withMessages(['selected_variants' => ['Jumlah untuk varian ini harus satu per buket.']]);
            }
        }

        return $variants;
    }

    private function currentCartItemGroup(int $cartItemGroupId): CartItemGroup
    {
        $cart = $this->getCurrentCart();

        if (! $cart) {
            throw (new ModelNotFoundException)->setModel(CartItemGroup::class, [$cartItemGroupId]);
        }

        return $cart->itemGroups()->findOrFail($cartItemGroupId);
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

    private function groupsMatch(CartItemGroup $first, CartItemGroup $second): bool
    {
        return $first->product_id === $second->product_id
            && $first->card_message === $second->card_message
            && $first->special_note === $second->special_note
            && $first->variants->pluck('quantity_in_bundle', 'product_variant_id')->sortKeys()->all() === $second->variants->pluck('quantity_in_bundle', 'product_variant_id')->sortKeys()->all();
    }

    private function sessionId(): string
    {
        return $this->request->session()->getId();
    }
}
