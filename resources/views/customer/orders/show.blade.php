@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">← Kembali ke riwayat pesanan</a>
        <div class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7"><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pesanan</p><div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-end"><div><h1 class="font-mono text-xl font-bold tracking-wide text-stone-800">{{ $order->order_number }}</h1><p class="mt-2 text-sm text-stone-500">Pengiriman {{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p></div><x-status-badge :status="$order->status->label()" /></div></div>
        <div class="mt-6"><x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" /></div>
        @if($order->status === \App\Enums\OrderStatus::DELIVERED)
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Setelah pesanan diterima</p>
                <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Bagaimana buketmu?</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Ulasanmu akan tampil setelah ditinjau oleh tim kami.</p>
                <div class="mt-5 divide-y divide-stone-100">
                    @foreach($order->items as $item)
                        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-stone-800">{{ $item->product_name }}</p>@if($item->variant_label)<p class="mt-1 text-sm text-stone-500">{{ $item->variant_label }}</p>@endif</div>@if($item->review)<button type="button" disabled class="inline-flex items-center justify-center rounded-xl bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-500">Ulasan Terkirim · {{ $item->review->status->label() }}</button>@else<x-review-form :action="route('customer.orders.reviews.store', [$order, $item])" :order-item="$item" />@endif</div>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection
