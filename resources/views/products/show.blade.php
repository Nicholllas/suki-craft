@extends('layouts.store')

@section('title', $product->name . ' | Suki Craft')
@section('description', $product->description ?: __('product.meta_description'))

@section('content')
    @php
        $imagePath = $product->primary_image?->path ?? $product->image;
        $activeVariants = $product->variants->where('is_active', true)->values();
        $variantPrices = $activeVariants->map(fn ($variant) => [
            'id' => $variant->id,
            'isQuantityBased' => $variant->is_quantity_based,
            'price' => (float) $variant->price_adjustment,
        ])->values();
        $bouquetSizes = $product->bouquetSizes->where('is_active', true)->values();
        $customBouquetCategory = $product->customBouquetCategory;
        $customRequestUrl = $customBouquetCategory ? route('custom-requests.create', ['category' => $customBouquetCategory->slug]) : null;
        $bouquetSizeOptions = $bouquetSizes->map(fn ($size) => [
            'label' => $size->label,
            'maxSheets' => $size->max_sheets,
            'minSheets' => $size->min_sheets,
            'rangeLabel' => $size->range_label,
            'servicePrice' => (float) $size->service_price,
        ])->all();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8" x-data="{
        activeImage: @js($imagePath ? Storage::url($imagePath) : null),
        allowMultipleVariants: @js($product->allow_multiple_variants),
        basePrice: {{ (float) $product->base_price }},
        bouquetSizes: @js($bouquetSizeOptions),
        customRequestThreshold: @js($customBouquetCategory?->quote_threshold),
        cardMessage: '',
        quantity: 1,
        selectedVariants: @js($activeVariants->isNotEmpty() && ! $product->allow_multiple_variants ? [$activeVariants->first()->id => 1] : []),
        specialNote: '',
        submitting: false,
        toast: '',
        toastType: 'success',
        variants: @js($variantPrices),
        formatPrice(value) { return new Intl.NumberFormat(@js(app()->isLocale('en') ? 'en-US' : 'id-ID')).format(value); },
        requiresCustomRequest() { return this.customRequestThreshold !== null && this.moneySheetCount() >= this.customRequestThreshold; },
        isSelected(variantId) { return Object.hasOwn(this.selectedVariants, variantId); },
        cartPayload(form) { const payload = new FormData(form); [...payload.keys()].filter((key) => key.startsWith('selected_variants[')).forEach((key) => payload.delete(key)); Object.entries(this.selectedVariants).forEach(([variantId, variantQuantity]) => payload.set(`selected_variants[${variantId}]`, variantQuantity)); return payload; },
        moneySheetCount() { return this.variants.filter((variant) => variant.isQuantityBased).reduce((total, variant) => total + Number(this.selectedVariants[variant.id] ?? 0), 0); },
        selectedBouquetSize() { const sheets = this.moneySheetCount(); return this.bouquetSizes.find((size) => sheets >= size.minSheets && (size.maxSheets === null || sheets <= size.maxSheets)) ?? null; },
        selectedPrice() { return this.servicePrice() + this.variants.reduce((total, variant) => total + (this.selectedVariants[variant.id] ?? 0) * variant.price, 0); },
        servicePrice() { const size = this.selectedBouquetSize(); return size ? size.servicePrice : this.basePrice; },
        setVariantQuantity(variantId, value) { const quantity = Number.parseInt(value, 10); if (Number.isInteger(quantity) && quantity > 0) { this.selectedVariants[variantId] = Math.min(999, quantity); } else { delete this.selectedVariants[variantId]; } },
        totalPrice() { return this.selectedPrice() * this.quantity; },
        toggleVariant(variantId) { if (this.isSelected(variantId)) { delete this.selectedVariants[variantId]; } else { this.selectedVariants[variantId] = 1; } },
        async addToCart(event) {
            this.submitting = true;
            this.toast = '';

            try {
                const response = await fetch(event.target.action, {
                    body: this.cartPayload(event.target),
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    method: 'POST',
                });
                const contentType = response.headers.get('content-type') ?? '';
                const payload = await response.text();
                const data = contentType.includes('application/json') && payload ? JSON.parse(payload) : {};

                if (!response.ok) {
                    const validationMessage = Object.values(data.errors ?? {}).flat()[0];
                    throw new Error(validationMessage || data.message || @js(__('product.cart_update_failed')));
                }

                if (!data.message) {
                    throw new Error(@js(__('product.cart_invalid_response')));
                }

                this.toast = data.message;
                this.toastType = 'success';
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.count } }));
            } catch (error) {
                this.toast = error.message || @js(__('product.cart_retry'));
                this.toastType = 'error';
            } finally {
                this.submitting = false;
            }
        },
    }">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            {{ __('product.back_to_collections') }}
        </a>

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,.95fr)] lg:gap-16">
            <div>
                <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 via-orange-50 to-amber-100">
                    <template x-if="activeImage">
                        <img :src="activeImage" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!activeImage">
                        <div class="grid h-full place-items-center text-7xl text-rose-300">✿</div>
                    </template>
                </div>

                @if($product->images->count() > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach($product->images as $image)
                            <button type="button" @click="activeImage = '{{ Storage::url($image->path) }}'" :class="activeImage === '{{ Storage::url($image->path) }}' ? 'ring-rose-500' : 'ring-transparent hover:ring-rose-200'" class="aspect-square overflow-hidden rounded-xl ring-2 transition focus:outline-none focus:ring-rose-500">
                                <img src="{{ Storage::url($image->path) }}" alt="{{ __('product.image_view', ['number' => $loop->iteration, 'product' => $product->name]) }}" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="self-center">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $product->category->name }}</p>
                <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">{{ $product->name }}</h1>
                <div class="mt-4 flex items-center gap-2"><div class="flex text-amber-400" aria-label="{{ __('product.average_rating', ['rating' => number_format($averageRating, 1)]) }}">@foreach(range(1, 5) as $star)<span>{{ $averageRating >= $star ? '★' : '☆' }}</span>@endforeach</div><span class="text-sm font-semibold text-stone-700">{{ number_format($averageRating, 1, app()->isLocale('en') ? '.' : ',', app()->isLocale('en') ? ',' : '.') }}</span><span class="text-sm text-stone-400">{{ __('product.reviews_count', ['count' => $reviews->total()]) }}</span></div>
                <p class="mt-5 text-base leading-7 text-stone-600">{{ $product->description ?: __('product.fallback_description') }}</p>

                <div class="mt-7 border-y border-stone-200 py-5">
                    <p class="text-sm text-stone-500">{{ __('product.price_for_selection') }}</p>
                    <p x-show="!requiresCustomRequest()" class="mt-1 font-serif text-3xl font-semibold text-stone-800">Rp<span x-text="formatPrice(totalPrice())"></span></p>
                    <p x-cloak x-show="requiresCustomRequest()" class="mt-1 font-serif text-2xl font-semibold text-stone-800">{{ __('product.price_confirmed') }}</p>
                </div>

                @if($bouquetSizes->isNotEmpty())
                    <section class="mt-7 rounded-2xl border border-rose-100 bg-rose-50/60 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-stone-800">{{ __('product.bouquet_size') }}</p>
                            </div>
                            <template x-if="selectedBouquetSize()">
                                <div class="rounded-xl bg-white px-3 py-2 text-right shadow-sm">
                                    <p class="text-xs font-semibold text-rose-600" x-text="`${selectedBouquetSize().label} · ${moneySheetCount()} {{ __('product.sheets', ['count' => '']).'' }}`"></p>
                                    <p x-show="!requiresCustomRequest()" class="mt-1 text-xs text-stone-600">{{ __('product.service_price') }}<span x-text="formatPrice(servicePrice())"></span></p>
                                    <p x-show="requiresCustomRequest()" class="mt-1 text-xs text-rose-600">{{ __('product.continue_custom_request') }}</p>
                                </div>
                            </template>
                        </div>
                        <p x-cloak x-show="!selectedBouquetSize()" class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-800">{{ __('product.custom_request_hint') }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($bouquetSizes as $size)
                                <div :class="selectedBouquetSize()?.label === @js($size->label) ? 'border-rose-300 bg-white ring-1 ring-rose-200' : 'border-transparent bg-white/70'" class="rounded-xl border px-3 py-2 text-xs text-stone-600">
                                    <span class="font-semibold text-stone-800">{{ $size->label }}</span> · {{ $size->range_label }}
                                    <span>· {{ __('product.service_price') }}{{ number_format($size->service_price, 0, app()->isLocale('en') ? '.' : ',', app()->isLocale('en') ? ',' : '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
                <form method="POST" action="{{ route('cart.add') }}" class="mt-7 space-y-7" @submit.prevent="addToCart($event)">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($activeVariants->isNotEmpty())
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800">{{ __('product.select_variant') }}</legend>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach($activeVariants as $variant)
                                    <div :class="isSelected({{ $variant->id }}) ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-stone-200 bg-white text-stone-700 hover:border-rose-200'" class="rounded-xl border p-4 text-sm transition">
                                        <label class="flex cursor-pointer items-center justify-between gap-3"><span class="flex items-center gap-3">@if ($product->allow_multiple_variants)<input type="checkbox" :checked="isSelected({{ $variant->id }})" @change="toggleVariant({{ $variant->id }})" class="h-4 w-4 rounded border-stone-300 text-rose-500">@else<input type="radio" name="variant-selection" :checked="isSelected({{ $variant->id }})" @change="selectedVariants = { {{ $variant->id }}: 1 }" class="h-4 w-4 border-stone-300 text-rose-500">@endif<span class="font-semibold">{{ $variant->label }}</span></span><span class="text-xs">{{ $variant->price_adjustment > 0 ? '+' : ($variant->price_adjustment < 0 ? '-' : '') }}Rp{{ number_format(abs($variant->price_adjustment), 0, ',', '.') }}</span></label>
                                        @if ($variant->is_quantity_based)
                                            <label :class="isSelected({{ $variant->id }}) ? 'border-rose-100 text-rose-700' : 'border-stone-100 text-stone-500'" class="mt-3 flex items-center justify-between gap-3 border-t pt-3 text-xs font-semibold transition"><span>{{ __('product.quantity_in_bouquet') }}</span><input type="number" :name="isSelected({{ $variant->id }}) ? 'selected_variants[{{ $variant->id }}]' : null" min="1" max="999" :value="selectedVariants[{{ $variant->id }}] ?? ''" @input="setVariantQuantity({{ $variant->id }}, $event.target.value)" placeholder="0" class="h-9 w-20 rounded-lg border-stone-200 bg-white px-2 text-center text-sm text-stone-800 placeholder:text-stone-300 focus:border-rose-300 focus:ring-rose-200"></label>
                                        @else
                                            <input type="hidden" name="selected_variants[{{ $variant->id }}]" value="1" :disabled="!isSelected({{ $variant->id }})">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <div>
                        <div class="flex items-end justify-between gap-4"><label for="card-message" class="text-sm font-semibold text-stone-800">{{ __('product.card_message') }} <span class="font-normal text-stone-400">{{ __('product.optional') }}</span></label><span class="text-xs text-stone-400"><span x-text="cardMessage.length"></span>/200</span></div>
                        <textarea id="card-message" name="card_message" x-model="cardMessage" maxlength="200" rows="3" placeholder="{{ __('product.card_message_placeholder') }}" class="mt-3 w-full rounded-xl border-stone-200 bg-white px-4 py-3 text-sm leading-6 text-stone-700 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200"></textarea>
                    </div>

                    <div>
                        <div class="flex items-end justify-between gap-4"><label for="special-note" class="text-sm font-semibold text-stone-800">{{ __('product.florist_note') }} <span class="font-normal text-stone-400">{{ __('product.optional') }}</span></label><span class="text-xs text-stone-400"><span x-text="specialNote.length"></span>/300</span></div>
                        <textarea id="special-note" name="special_note" x-model="specialNote" maxlength="300" rows="2" placeholder="{{ __('product.florist_note_placeholder') }}" class="mt-3 w-full rounded-xl border-stone-200 bg-white px-4 py-3 text-sm leading-6 text-stone-700 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-stone-50 p-4">
                        <div><p class="text-sm font-semibold text-stone-800">{{ __('product.bouquet_quantity') }}</p><p class="mt-0.5 text-xs text-stone-500">{{ __('product.minimum_bouquet') }}</p></div>
                        <div class="flex items-center rounded-xl border border-stone-200 bg-white">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="grid h-10 w-10 place-items-center text-lg text-stone-500 transition hover:text-rose-600" aria-label="{{ __('product.decrease_quantity') }}">−</button>
                            <input type="number" name="bundle_quantity" x-model.number="quantity" min="1" max="99" class="h-10 w-10 border-0 bg-transparent p-0 text-center text-sm font-semibold text-stone-800 focus:ring-0">
                            <button type="button" @click="quantity = Math.min(99, quantity + 1)" class="grid h-10 w-10 place-items-center text-lg text-stone-500 transition hover:text-rose-600" aria-label="{{ __('product.increase_quantity') }}">+</button>
                        </div>
                    </div>

                    <button x-show="!requiresCustomRequest()" type="submit" :disabled="submitting" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-rose-500 px-6 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 disabled:cursor-wait disabled:opacity-70">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h1.15c.67 0 1.25.47 1.39 1.13l.22 1.04m0 0 1.2 5.67a1.5 1.5 0 0 0 1.47 1.19h7.59a1.5 1.5 0 0 0 1.44-1.08l1.06-3.68a.75.75 0 0 0-.72-.96H6.51" /></svg>
                        <span x-text="submitting ? @js(__('product.adding')) : @js(__('product.add_to_cart'))"></span>
                    </button>
                    @if ($customRequestUrl)
                        <a x-cloak x-show="requiresCustomRequest()" href="{{ $customRequestUrl }}" class="inline-flex h-14 w-full items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-6 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">{{ __('product.continue_custom_request') }}</a>
                    @endif
                    <p x-cloak x-show="toast" x-text="toast" x-transition :class="toastType === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" class="rounded-xl px-4 py-3 text-center text-sm font-medium" role="status"></p>
                </form>

                <div class="mt-7 rounded-2xl bg-rose-50 p-5"><div class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.66 5.1H19l-4.33 3.15 1.66 5.1L12 13.2l-4.33 3.15 1.66-5.1L5 8.1h5.34L12 3Z" /></svg><div><p class="text-sm font-semibold text-stone-800">{{ __('product.made_to_order') }}</p><p class="mt-1 text-sm leading-6 text-stone-600">{{ __('product.made_to_order_detail') }}</p></div></div></div>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 sm:pb-16 lg:px-8">
        <div class="border-t border-stone-200 pt-10 sm:pt-14">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ __('product.customer_reviews') }}</p><h2 class="mt-2 font-serif text-3xl font-semibold text-stone-800">{{ __('product.review_heading') }}</h2></div><div class="flex items-center gap-2 text-sm text-stone-500"><span class="text-amber-400">★</span><span>{{ __('product.rating_summary', ['rating' => number_format($averageRating, 1, app()->isLocale('en') ? '.' : ',', app()->isLocale('en') ? ',' : '.'), 'count' => $reviews->total()]) }}</span></div></div>

            @forelse($reviews as $review)
                <article class="mt-6 rounded-2xl border border-stone-200 bg-white p-5 sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div><p class="font-semibold text-stone-800">{{ $review->reviewer_name }}</p><div class="mt-2 flex text-amber-400" aria-label="{{ __('product.review_rating', ['rating' => $review->rating]) }}">@foreach(range(1, 5) as $star)<span>{{ $review->rating >= $star ? '★' : '☆' }}</span>@endforeach</div></div><p class="text-xs text-stone-400">{{ ($review->reviewed_at ?? $review->created_at)->locale(app()->getLocale())->translatedFormat('d F Y') }}</p></div>
                    @if($review->comment)<p class="mt-4 text-sm leading-7 text-stone-600">{{ $review->comment }}</p>@endif
                    @if($review->photo_path)<a href="{{ Storage::url($review->photo_path) }}" target="_blank" rel="noopener noreferrer" class="mt-4 block overflow-hidden rounded-2xl border border-stone-100"><img src="{{ Storage::url($review->photo_path) }}" alt="{{ __('product.review_photo', ['name' => $review->reviewer_name]) }}" class="max-h-96 w-full object-cover"></a>@endif
                </article>
            @empty
                <div class="mt-6 rounded-2xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-10 text-center"><p class="font-serif text-xl font-semibold text-stone-800">{{ __('product.no_reviews') }}</p><p class="mt-2 text-sm text-stone-500">{{ __('product.no_reviews_detail') }}</p></div>
            @endforelse

            @if($reviews->hasPages())<div class="mt-7">{{ $reviews->links() }}</div>@endif
        </div>
    </section>
@endsection
