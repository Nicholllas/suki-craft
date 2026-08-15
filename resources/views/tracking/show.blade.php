@extends('layouts.store')

@section('title', 'Lacak ' . $order->order_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('tracking.create') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>Lacak pesanan lain</a>

        <div class="mt-5 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pesanan</p>
            <div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                <div><h1 class="font-mono text-xl font-bold tracking-wide text-stone-800">{{ $order->order_number }}</h1><p class="mt-2 text-sm text-stone-500">Pengiriman {{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p></div>
                <x-status-badge :status="$order->status->label()" />
            </div>
        </div>

        <div class="mt-6"><x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" /></div>
    </section>
@endsection
