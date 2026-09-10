@props(['item'])

@php
    $bundleQuantity = max(1, (int) $item->bundle_quantity);
    $pricePerBouquet = (float) $item->subtotal / $bundleQuantity;
    $servicePrice = (float) ($item->service_price ?? 0);
@endphp

<dl class="mt-3 space-y-2 rounded-xl bg-stone-50 px-3 py-3 text-xs leading-5 text-stone-600">
    <div class="flex items-start justify-between gap-4">
        <dt>
            {{ $item->custom_request_id ? __('storefront.order_price.custom_bouquet') : __('storefront.order_price.arrangement_fee') }}
            <span class="text-stone-400">{{ __('storefront.order_price.per_bouquet') }}</span>
        </dt>
        <dd class="shrink-0 font-semibold text-stone-700">
            @if ($item->requires_quote && $servicePrice === 0.0)
                {{ __('storefront.order_price.awaiting_quote') }}
            @else
                Rp{{ number_format($servicePrice, 0, ',', '.') }}
            @endif
        </dd>
    </div>

    @if ($item->custom_request_id)
        <div class="flex items-start justify-between gap-4">
            <dt>{{ __('storefront.order_price.contents') }}</dt>
            <dd class="shrink-0 text-stone-500">{{ __('storefront.order_price.approved_request') }}</dd>
        </div>
    @else
        @forelse ($item->variants as $variant)
            @php
                $quantityInBundle = (int) $variant->quantity_in_bundle;
                $unitPrice = (float) $variant->unit_price;
                $variantLabel = $variant->relationLoaded('productVariant') ? $variant->productVariant?->label : $variant->variant_label;
                $variantSubtotal = $unitPrice * $quantityInBundle;
            @endphp
            <div class="flex items-start justify-between gap-4">
                <dt>
                    <span class="font-medium text-stone-700">{{ $variantLabel }}</span>
                    <span class="block text-stone-400">{{ __('storefront.order_price.variant_price', ['price' => number_format($unitPrice, 0, ',', '.'), 'quantity' => $quantityInBundle]) }}</span>
                </dt>
                <dd class="shrink-0 font-semibold text-stone-700">Rp{{ number_format($variantSubtotal, 0, ',', '.') }}</dd>
            </div>
        @empty
            <div class="flex items-start justify-between gap-4">
                <dt>{{ __('storefront.order_price.selected_variant') }}</dt>
                <dd class="shrink-0 text-stone-400">{{ __('storefront.order_price.none') }}</dd>
            </div>
        @endforelse
    @endif

    <div class="flex items-start justify-between gap-4 border-t border-stone-200 pt-2">
        <dt class="font-semibold text-stone-800">{{ __('storefront.order_price.one_bouquet') }}</dt>
        <dd class="shrink-0 font-semibold text-stone-800">Rp{{ number_format($pricePerBouquet, 0, ',', '.') }}</dd>
    </div>
    <div class="flex items-start justify-between gap-4">
        <dt>{{ __('storefront.order_price.bouquet_quantity', ['count' => $bundleQuantity, 'price' => number_format($pricePerBouquet, 0, ',', '.')]) }}</dt>
        <dd class="shrink-0 font-bold text-stone-800">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</dd>
    </div>
</dl>