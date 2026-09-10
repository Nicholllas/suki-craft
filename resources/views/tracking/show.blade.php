@extends('layouts.store')

@section('title', __('storefront.tracking.title', ['number' => $order->order_number]))

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('tracking.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            {{ __('storefront.tracking.back_to_tracking') }}
        </a>

        <div class="mt-5 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ __('storefront.tracking.order') }}</p>
            <div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div>
                    <h1 class="font-mono text-xl font-bold tracking-wide text-stone-800">{{ $order->order_number }}</h1>
                    <p class="mt-2 text-sm text-stone-500">{{ __('storefront.tracking.delivery', ['date' => $order->delivery_date->locale(app()->getLocale())->translatedFormat('d F Y'), 'time' => __('delivery.slots.'.$order->delivery_time_slot)]) }}</p>
                </div>
                <x-status-badge :status="$order->status->label()" />
            </div>
        </div>

        <div class="mt-6"><x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" /></div>

        @if ($order->status === \App\Enums\OrderStatus::DELIVERED)
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ __('storefront.tracking.after_delivery') }}</p>
                <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">{{ __('storefront.tracking.review_heading') }}</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">{{ __('storefront.tracking.review_description') }}</p>
                <div class="mt-5 divide-y divide-stone-100">
                    @foreach ($order->itemGroups as $item)
                        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-800">{{ $item->custom_request_id ? __('storefront.tracking.custom_bouquet', ['number' => $item->customRequest?->request_number ?? '']) : $item->product_name }}<x-bouquet-size-label :item="$item" /></p>
                                @if ($item->variants->isNotEmpty())
                                    <ul class="mt-1 text-sm text-stone-500">
                                        @foreach ($item->variants as $variant)
                                            <li>{{ $variant->variant_label }}@if ($variant->quantity_in_bundle > 1) · {{ $variant->quantity_in_bundle }}×@endif</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @if ($item->review)
                                <button type="button" disabled class="inline-flex items-center justify-center rounded-xl bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-500">{{ __('storefront.tracking.review_sent', ['status' => $item->review->status->label()]) }}</button>
                            @else
                                <x-review-form :action="route('tracking.reviews.store', [$order, $item])" :order-item="$item" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection
