@props(['item'])

@php
    $bundleQuantity = max(1, (int) $item->bundle_quantity);
    $pricePerBouquet = (float) $item->subtotal / $bundleQuantity;
    $servicePrice = (float) ($item->service_price ?? 0);
@endphp

<dl class="mt-3 space-y-2 rounded-xl bg-stone-50 px-3 py-3 text-xs leading-5 text-stone-600">
    <div class="flex items-start justify-between gap-4">
        <dt>Biaya jasa merangkai <span class="text-stone-400">per buket</span></dt>
        <dd class="shrink-0 font-semibold text-stone-700">
            @if ($item->requires_quote && $servicePrice === 0.0)
                Menunggu penawaran
            @else
                Rp{{ number_format($servicePrice, 0, ',', '.') }}
            @endif
        </dd>
    </div>

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
                <span class="block text-stone-400">Varian terpilih · Rp{{ number_format($unitPrice, 0, ',', '.') }} × {{ $quantityInBundle }}</span>
            </dt>
            <dd class="shrink-0 font-semibold text-stone-700">Rp{{ number_format($variantSubtotal, 0, ',', '.') }}</dd>
        </div>
    @empty
        <div class="flex items-start justify-between gap-4">
            <dt>Varian terpilih</dt>
            <dd class="shrink-0 text-stone-400">Tidak ada</dd>
        </div>
    @endforelse

    <div class="flex items-start justify-between gap-4 border-t border-stone-200 pt-2">
        <dt class="font-semibold text-stone-800">Harga 1 buket</dt>
        <dd class="shrink-0 font-semibold text-stone-800">Rp{{ number_format($pricePerBouquet, 0, ',', '.') }}</dd>
    </div>
    <div class="flex items-start justify-between gap-4">
        <dt>{{ $bundleQuantity }} buket × Rp{{ number_format($pricePerBouquet, 0, ',', '.') }}</dt>
        <dd class="shrink-0 font-bold text-stone-800">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</dd>
    </div>
</dl>
