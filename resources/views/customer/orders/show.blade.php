@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">← Kembali ke riwayat pesanan</a>
        <div class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7"><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pesanan</p><div class="mt-2 flex flex-col justify-between gap-3 sm:flex-row sm:items-end"><div><h1 class="font-mono text-xl font-bold tracking-wide text-stone-800">{{ $order->order_number }}</h1><p class="mt-2 text-sm text-stone-500">Pengiriman {{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p></div><x-status-badge :status="$order->status->label()" /></div></div>
        <div class="mt-6"><x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" /></div>
    </section>
@endsection
