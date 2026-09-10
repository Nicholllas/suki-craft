@extends('layouts.store')

@section('title', __('storefront.checkout.title'))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            {{ __('storefront.checkout.back_to_cart') }}
        </a>

        <div class="mt-7 border-b border-stone-200 pb-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">{{ __('storefront.checkout.eyebrow') }}</p>
            <h1 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">{{ __('storefront.checkout.heading') }}</h1>
            <div class="mt-5 flex max-w-md items-center gap-2 text-xs font-semibold">
                <span class="flex items-center gap-2 text-rose-600"><span class="grid h-6 w-6 place-items-center rounded-full bg-rose-500 text-white">1</span>{{ __('storefront.checkout.delivery_data') }}</span>
                <span class="h-px flex-1 bg-rose-200"></span>
                <span class="flex items-center gap-2 text-stone-400"><span class="grid h-6 w-6 place-items-center rounded-full border border-stone-200 bg-white">2</span>{{ __('storefront.checkout.confirmation') }}</span>
            </div>
        </div>

        @if ($errors->has('cart'))
            <div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first('cart') }}</div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" x-data="checkoutForm" x-on:submit="submitCheckout($event)" class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">
            @csrf
            <input type="hidden" name="idempotency_token" value="{{ session('checkout.idempotency_token') }}">

            <div class="space-y-6">
                <section x-data="deliverySchedule({{ Illuminate\Support\Js::from($timeSlots) }}, '{{ now('Asia/Jakarta')->toDateString() }}', '{{ now('Asia/Jakarta')->format('H:i') }}', {{ (int) config('delivery.same_day_prep_hours', 4) }}, '{{ old('delivery_date') }}', '{{ old('delivery_time_slot') }}')" class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-50 text-sm font-bold text-rose-600">1</span>
                        <div><h2 class="font-serif text-2xl font-semibold text-stone-800">{{ __('storefront.checkout.recipient_title') }}</h2><p class="mt-1 text-sm leading-6 text-stone-500">{{ __('storefront.checkout.recipient_intro') }}</p></div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer-name" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.recipient_name') }}</label>
                            <input id="customer-name" name="customer_name" type="text" value="{{ old('customer_name', $customer?->name) }}" autocomplete="name" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_name') border-rose-400 @enderror" placeholder="{{ __('storefront.checkout.recipient_name_placeholder') }}">
                            @error('customer_name')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer-phone" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.whatsapp') }}</label>
                            <input id="customer-phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $customer?->phone) }}" inputmode="tel" autocomplete="tel" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_phone') border-rose-400 @enderror" placeholder="08xxxxxxxxxx">
                            <p class="mt-2 text-xs text-stone-400">{{ __('storefront.checkout.phone_hint') }}</p>
                            @error('customer_phone')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer-email" class="text-sm font-semibold text-stone-700">Email <span class="font-normal text-stone-400">({{ __('storefront.checkout.optional') }})</span></label>
                            <input id="customer-email" name="customer_email" type="email" value="{{ old('customer_email', $customer?->email) }}" autocomplete="email" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_email') border-rose-400 @enderror" placeholder="{{ __('storefront.checkout.email_placeholder') }}">
                            @error('customer_email')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-50 text-sm font-bold text-rose-600">2</span>
                        <div><h2 class="font-serif text-2xl font-semibold text-stone-800">{{ __('storefront.checkout.delivery_title') }}</h2><p class="mt-1 text-sm leading-6 text-stone-500">{{ __('storefront.checkout.delivery_intro') }}</p></div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="delivery-date" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.delivery_date') }}</label>
                            <input id="delivery-date" name="delivery_date" type="date" value="{{ old('delivery_date') }}" min="{{ $minimumDeliveryDate }}" x-model="selectedDate" x-on:change="clearUnavailableSlot" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 focus:border-rose-300 focus:ring-rose-200 @error('delivery_date') border-rose-400 @enderror">
                            <p class="mt-2 text-xs text-stone-400">{{ __('storefront.checkout.delivery_date_hint') }}</p>
                            @error('delivery_date')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="delivery-time-slot" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.arrival_time') }}</label>
                            <select id="delivery-time-slot" name="delivery_time_slot" x-model="selectedSlot" required class="mt-2 h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 focus:border-rose-300 focus:ring-rose-200 @error('delivery_time_slot') border-rose-400 @enderror">
                                <option value="">{{ __('storefront.checkout.choose_delivery_time') }}</option>
                                @foreach ($timeSlots as $value => $slot)
                                    <option value="{{ $value }}" :disabled="!isSlotAvailable(slots['{{ $value }}'])" @selected(old('delivery_time_slot') === $value)>{{ __('delivery.slots.'.$value) }}</option>
                                @endforeach
                            </select>
                            @error('delivery_time_slot')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="delivery-address" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.delivery_address') }}</label>
                            <textarea id="delivery-address" name="delivery_address" rows="4" required class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('delivery_address') border-rose-400 @enderror" placeholder="{{ __('storefront.checkout.address_placeholder') }}">{{ old('delivery_address', $customer?->address) }}</textarea>
                            @error('delivery_address')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="text-sm font-semibold text-stone-700">{{ __('storefront.checkout.additional_notes') }} <span class="font-normal text-stone-400">({{ __('storefront.checkout.optional') }})</span></label>
                            <textarea id="notes" name="notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('notes') border-rose-400 @enderror" placeholder="{{ __('storefront.checkout.notes_placeholder') }}">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside data-checkout-summary class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm lg:sticky lg:top-24 lg:p-6 lg:shadow-xl lg:shadow-stone-900/5" x-data="checkoutSummary({{ Illuminate\Support\Js::from($checkoutPricing) }}, '{{ route('checkout.promotions.validate') }}', '{{ csrf_token() }}', {{ Illuminate\Support\Js::from(['promoFailed' => __('storefront.checkout.promo_failed'), 'promoNetworkFailed' => __('storefront.checkout.promo_network_failed')]) }})">
                <div class="flex items-center justify-between gap-4">
                    <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">{{ __('storefront.checkout.order_eyebrow') }}</p><h2 class="mt-1 font-serif text-2xl font-semibold text-stone-800">{{ __('storefront.checkout.summary') }}</h2></div>
                    <button type="button" @click="showItems = !showItems" :aria-expanded="showItems" class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 lg:hidden"><span x-text="showItems ? '{{ __('storefront.checkout.close') }}' : '{{ __('storefront.checkout.view_bouquets') }}'"></span></button>
                </div>

                <div x-cloak x-show="showItems" x-transition class="mt-5 space-y-4 border-b border-stone-100 pb-5">
                    @foreach ($cart->itemGroups as $item)
                        @php($imagePath = $item->product->primary_image?->path ?? $item->product->image)
                        <div class="flex gap-3">
                            <div class="h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-rose-50">
                                @if ($imagePath)
                                    <img src="{{ Storage::url($imagePath) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full place-items-center text-xl text-rose-300">✿</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-stone-800">{{ $item->custom_request_id ? __('storefront.checkout.custom_bouquet', ['number' => $item->customRequest?->request_number ?? '']) : $item->product->name }}<x-bouquet-size-label :item="$item" /></p>
                                <x-order-item-price-breakdown :item="$item" />
                            </div>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between text-stone-500"><dt>{{ __('storefront.checkout.subtotal') }}</dt><dd class="font-medium text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex items-center justify-between text-stone-500"><dt>{{ __('storefront.checkout.delivery_fee') }}</dt><dd class="font-medium text-stone-800">Rp{{ number_format($deliveryFee, 0, ',', '.') }}</dd></div>
                    <div class="flex items-center justify-between border-t border-stone-100 pt-3 text-stone-600"><dt class="font-semibold">{{ __('storefront.checkout.total_before_discount') }}</dt><dd class="font-semibold">Rp{{ number_format($checkoutPricing['total_before_discount'], 0, ',', '.') }}</dd></div>
                    <div class="rounded-xl bg-rose-50 p-3">
                        <label for="promotion-code" class="text-xs font-semibold text-stone-700">{{ __('storefront.checkout.promo_code') }}</label>
                        <div class="mt-2 flex gap-2">
                            <input id="promotion-code" name="promotion_code" x-model="code" class="min-w-0 flex-1 rounded-lg border-stone-200 px-3 py-2 text-sm uppercase focus:border-rose-300 focus:ring-rose-200" placeholder="PROMO2026">
                            <button type="button" @click="applyPromotion" :disabled="loading" class="rounded-lg bg-stone-800 px-3 text-xs font-semibold text-white disabled:opacity-60" x-text="loading ? '...' : '{{ __('storefront.checkout.apply') }}'"></button>
                        </div>
                        <p x-show="error" x-text="error" class="mt-2 text-xs text-rose-600">{{ $checkoutPricing['message'] ?? '' }}</p>
                        @error('promotion_code')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-show="discount > 0" @if (! $checkoutPricing['discount_amount']) x-cloak @endif class="flex items-center justify-between gap-4 text-emerald-600">
                        <dt class="font-medium">{{ __('storefront.checkout.promo_applied') }} <span x-text="code">{{ $checkoutPricing['code'] }}</span><span x-show="promotionType === 'percentage'" x-text="' (' + format(promotionValue) + '%)'"> ({{ $checkoutPricing['promotion_value'] }}%)</span></dt>
                        <dd class="shrink-0 font-semibold">-Rp<span x-text="format(discount)">{{ number_format($checkoutPricing['discount_amount'], 0, ',', '.') }}</span></dd>
                    </div>
                    <div class="flex items-end justify-between border-t border-stone-100 pt-4"><dt class="font-semibold text-stone-800">{{ __('storefront.checkout.total_after_discount') }}</dt><dd class="font-serif text-2xl font-semibold text-stone-800">Rp<span x-text="format(total)">{{ number_format($checkoutPricing['total'], 0, ',', '.') }}</span></dd></div>
                </dl>
                <p class="mt-3 text-xs leading-5 text-stone-500">{{ __('storefront.checkout.total_formula') }}</p>

                <button type="submit" x-bind:disabled="isSubmitting" x-bind:aria-busy="isSubmitting" class="mt-6 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">
                    <span x-cloak x-show="!isSubmitting">{{ __('storefront.checkout.submit') }}</span>
                    <span x-cloak x-show="isSubmitting">Memproses pesanan...</span>
                    <svg x-cloak x-show="isSubmitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" class="opacity-25" /><path d="M21 12a9 9 0 0 1-9 9" class="opacity-90" /></svg>
                    <svg x-cloak x-show="!isSubmitting" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                </button>
                <p class="mt-3 text-center text-xs leading-5 text-stone-400">{{ __('storefront.checkout.payment_note') }}</p>
            </aside>
        </form>
    </section>
@endsection
