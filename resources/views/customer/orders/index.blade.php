@extends('layouts.store')

@section('title', 'Riwayat Pesanan | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akun Suki Craft</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Riwayat pesanan</h1><p class="mt-2 text-sm text-stone-500">Lihat status dan detail pesanan yang dibuat dari akun Anda.</p></div>
        <div class="mt-6"><x-customer.account-navigation active="orders" /></div>
        <div class="mt-6 space-y-4">
            @forelse($orders as $order)
                <a href="{{ route('customer.orders.show', $order) }}" class="block rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-rose-200 hover:shadow-md"><div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-mono text-sm font-bold tracking-wide text-stone-800">{{ $order->order_number }}</p><p class="mt-2 text-sm text-stone-500">{{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot.'.label', $order->delivery_time_slot) }}</p><p class="mt-1 text-xs text-stone-400">Dibuat {{ $order->created_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p></div><div class="flex items-center justify-between gap-4 sm:justify-end"><x-status-badge :status="$order->status->label()" /><span class="text-sm font-semibold text-rose-600">Detail →</span></div></div></a>
            @empty
                <div class="rounded-3xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-14 text-center"><div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-2xl text-rose-400">✿</div><h2 class="mt-5 font-serif text-2xl font-semibold text-stone-800">Belum ada pesanan</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">Pesanan yang dibuat setelah masuk ke akun ini akan muncul di sini.</p><a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Jelajahi buket</a></div>
            @endforelse
        </div>
        @if($orders->hasPages())<div class="mt-7">{{ $orders->links() }}</div>@endif
    </section>
@endsection
