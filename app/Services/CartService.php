<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItemGroup;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Product;
use App\Models\ProductBouquetSize;
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
            throw ValidationException::withMessages(['bundle_quantity' => [__('store.validation.messages.cart_bundle_quantity')]]);
        }

        $selectedVariants = collect($selectedVariants)->mapWithKeys(fn ($quantity, $variantId): array => [(int) $variantId => (int) $quantity])->all();

        return DB::transaction(function () use ($bundleQuantity, $customizations, $product, $selectedVariants): CartItemGroup {
            $product = Product::query()->where('is_active', true)->whereHas('category', fn ($query) => $query->where('is_active', true))->findOrFail($product->id);

            if ($product->is_custom_request) {
                throw ValidationException::withMessages(['product' => [__('store.validation.messages.cart_custom_request_only')]]);
            }

            $variants = $this->resolveVariants($product, $selectedVariants);
            $this->ensureQuantityUsesCustomRequestWhenRequired($product, $variants, $selectedVariants);
            $bouquetSize = $this->resolveBouquetSize($product, $variants, $selectedVariants);
            $cart = $this->findOrCreateCurrentCart();
            $attributes = $this->customizationAttributes($customizations);
            $group = $cart->itemGroups()->create([
                ...$attributes,
                'bouquet_size_id' => $bouquetSize?->id,
                'bouquet_size_label' => $bouquetSize?->label,
                'bundle_quantity' => $bundleQuantity,
                'product_id' => $product->id,
                'requires_quote' => $bouquetSize?->is_custom ?? false,
                'service_price' => $bouquetSize?->is_custom ? 0 : ($bouquetSize?->service_price ?? $product->base_price),
            ]);

            $group->variants()->createMany($variants->map(fn (ProductVariant $variant): array => [
                'product_variant_id' => $variant->id,
                'quantity_in_bundle' => $selectedVariants[$variant->id],
                'unit_price' => $variant->price_adjustment,
            ])->all());

            return $group->load(['bouquetSize', 'product', 'variants.productVariant']);
        });
    }

    public function addApprovedCustomRequest(CustomRequest $customRequest, Customer $customer): CartItemGroup
    {
        return DB::transaction(function () use ($customRequest, $customer): CartItemGroup {
            $customRequest = CustomRequest::query()
                ->whereBelongsTo($customer, 'customer')
                ->lockForUpdate()
                ->findOrFail($customRequest->id);

            if ($customRequest->quoted_price === null) {
                throw ValidationException::withMessages(['custom_request' => [__('store.validation.messages.custom_quote_unavailable')]]);
            }

            $product = Product::query()
                ->where('is_active', true)
                ->where('is_custom_request', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->findOrFail($customRequest->product_id);
            $cart = Cart::query()->firstOrCreate(['customer_id' => $customer->id]);
            $cart->update(['checkout_processed_at' => null]);
            $existingGroup = $cart->itemGroups()->where('custom_request_id', $customRequest->id)->first();

            if ($existingGroup) {
                return $existingGroup->load(['bouquetSize', 'customRequest.items', 'product', 'variants.productVariant']);
            }

            $group = $cart->itemGroups()->create([
                'bundle_quantity' => 1,
                'custom_request_id' => $customRequest->id,
                'product_id' => $product->id,
                'requires_quote' => false,
                'service_price' => $customRequest->quoted_price,
            ]);

            return $group->load(['bouquetSize', 'customRequest.items', 'product', 'variants.productVariant']);
        });
    }

    public function updateQuantity(int $cartItemGroupId, int $quantity): CartItemGroup
    {
        $group = $this->currentCartItemGroup($cartItemGroupId);

        if ($group->custom_request_id !== null) {
            throw ValidationException::withMessages(['bundle_quantity' => [__('store.validation.messages.custom_quantity_locked')]]);
        }

        $group->update(['bundle_quantity' => $quantity]);

        return $group->refresh()->load(['bouquetSize', 'product', 'variants.productVariant']);
    }

    public function removeItem(int $cartItemGroupId): void
    {
        $this->currentCartItemGroup($cartItemGroupId)->delete();
    }

    public function getCurrentCart(): ?Cart
    {
        return Cart::query()->when($this->customerId(), fn ($query, $customerId) => $query->where('customer_id', $customerId), fn ($query) => $query->where('session_id', $this->sessionId()))->first();
    }

    public function mergeGuestCartIntoCustomer(int $customerId, string $guestCartSessionId): void
    {
        DB::transaction(function () use ($customerId, $guestCartSessionId) {
            $guestCart = Cart::query()->with('itemGroups.variants')->where('session_id', $guestCartSessionId)->first();

            if (! $guestCart) {
                return;
            }

            $customerCart = Cart::query()->firstOrCreate(['customer_id' => $customerId]);
            $customerCart->update(['checkout_processed_at' => null]);
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

        return $cart ? $cart->loadMissing(['itemGroups.product', 'itemGroups.variants'])->itemGroups->sum(fn (CartItemGroup $group): float => $group->subtotal) : 0;
    }

    public function getItemCount(): int
    {
        return (int) ($this->getCurrentCart()?->itemGroups()->sum('bundle_quantity') ?? 0);
    }

    private function findOrCreateCurrentCart(): Cart
    {
        $cart = $this->customerId() ? Cart::query()->firstOrCreate(['customer_id' => $this->customerId()]) : Cart::query()->firstOrCreate(['session_id' => $this->sessionId()]);
        $cart->update(['checkout_processed_at' => null]);

        return $cart;
    }

    private function resolveVariants(Product $product, array $selectedVariants): Collection
    {
        $selectedVariants = collect($selectedVariants)->mapWithKeys(fn ($quantity, $variantId): array => [(int) $variantId => (int) $quantity]);

        if (! $product->allow_multiple_variants && $selectedVariants->count() > 1) {
            throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_single_variant')]]);
        }

        $variants = $product->variants()->where('is_active', true)->whereIn('id', $selectedVariants->keys())->get();

        if ($variants->count() !== $selectedVariants->count()) {
            throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_variant_unavailable')]]);
        }

        if ($product->variants()->where('is_active', true)->exists() && $variants->isEmpty()) {
            throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_variant_required')]]);
        }

        foreach ($variants as $variant) {
            if ($variant->is_quantity_based && $selectedVariants[$variant->id] <= 0) {
                throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_variant_positive')]]);
            }

            if (! $variant->is_quantity_based && $selectedVariants[$variant->id] !== 1) {
                throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_variant_one')]]);
            }
        }

        return $variants;
    }

    private function ensureQuantityUsesCustomRequestWhenRequired(Product $product, Collection $variants, array $selectedVariants): void
    {
        $customBouquetCategory = $product->customBouquetCategory;

        if ($customBouquetCategory === null) {
            return;
        }

        $quantity = $variants->where('is_quantity_based', true)->sum(fn (ProductVariant $variant): int => $selectedVariants[$variant->id]);

        if ($customBouquetCategory->requiresCustomRequestForQuantity($quantity)) {
            throw ValidationException::withMessages([
                'selected_variants' => [__('store.validation.messages.cart_custom_quantity_quote', ['label' => $customBouquetCategory->quantity_label, 'threshold' => $customBouquetCategory->quote_threshold])],
            ]);
        }
    }

    private function resolveBouquetSize(Product $product, Collection $variants, array $selectedVariants): ?ProductBouquetSize
    {
        $sizes = $product->bouquetSizes()->where('is_active', true)->orderBy('min_sheets')->get();

        if ($sizes->isEmpty()) {
            return null;
        }

        $sheetCount = $variants->where('is_quantity_based', true)->sum(fn (ProductVariant $variant): int => $selectedVariants[$variant->id]);
        $bouquetSize = $sizes->first(fn (ProductBouquetSize $size): bool => $size->appliesToSheetCount($sheetCount));

        if (! $bouquetSize) {
            throw ValidationException::withMessages(['selected_variants' => [__('store.validation.messages.cart_bouquet_size_unavailable')]]);
        }

        return $bouquetSize;
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
        return $first->custom_request_id === $second->custom_request_id
            && $first->product_id === $second->product_id
            && $first->bouquet_size_id === $second->bouquet_size_id
            && $first->card_message === $second->card_message
            && $first->special_note === $second->special_note
            && (float) $first->service_price === (float) $second->service_price
            && $first->variants->pluck('quantity_in_bundle', 'product_variant_id')->sortKeys()->all() === $second->variants->pluck('quantity_in_bundle', 'product_variant_id')->sortKeys()->all();
    }

    private function sessionId(): string
    {
        return $this->request->session()->getId();
    }
}
