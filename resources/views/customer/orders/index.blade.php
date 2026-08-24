@extends('layouts.store')

@section('title', 'Riwayat Pesanan | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akun Suki Craft</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Riwayat pesanan</h1>
            <p class="mt-2 text-sm text-stone-500">Lihat status dan total setiap pesanan yang dibuat dari akun Anda.</p>
        </div>

        <div class="mt-6"><x-customer.account-navigation active="orders" /></div>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-mono text-sm font-bold tracking-wide text-stone-800">{{ $order->order_number }}</p>
                            <p class="mt-2 text-sm text-stone-500">Pengiriman {{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot.'.label', $order->delivery_time_slot) }}</p>
                            <p class="mt-1 text-xs text-stone-400">Dibuat {{ $order->created_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p>
                            <p class="mt-3 text-sm font-semibold text-stone-800">Total pembayaran <span class="ml-2">Rp{{ number_format($order->total, 0, ',', '.') }}</span></p>
                        </div>
                        <x-status-badge :status="$order->status->label()" />
                    </div>


                    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('customer.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Lihat detail pesanan</a>
                        @if ($order->whats_app_url)
                            <a href="{{ $order->whats_app_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.5 2 2 6.44 2 11.9c0 2.15.7 4.15 1.9 5.78L2.68 22l4.48-1.18a10.1 10.1 0 0 0 4.88 1.24h.01c5.53 0 10.03-4.44 10.03-9.9C22.08 6.44 17.58 2 12.04 2Zm0 18.4c-1.55 0-3.07-.41-4.39-1.19l-.32-.19-2.66.7.71-2.57-.21-.34a8.09 8.09 0 0 1-1.25-4.3c0-4.48 3.65-8.13 8.13-8.13 4.48 0 8.12 3.65 8.12 8.13 0 4.48-3.65 8.13-8.13 8.13Zm4.46-6.1c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06a6.55 6.55 0 0 1-1.93-1.18 7.17 7.17 0 0 1-1.32-1.62c-.14-.24-.01-.37.11-.49.11-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.66 4.13 3.73.58.25 1.03.4 1.38.5.58.18 1.11.15 1.53.09.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" /></svg>
                                Hubungi admin via WhatsApp
                            </a>
                        @else
                            <p class="text-xs text-stone-400">Nomor WhatsApp admin belum diatur.</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-14 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-2xl text-rose-400">✿</div>
                    <h2 class="mt-5 font-serif text-2xl font-semibold text-stone-800">Belum ada pesanan</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">Pesanan yang dibuat setelah masuk ke akun ini akan muncul di sini.</p>
                    <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Jelajahi buket</a>
                </div>
            @endforelse
        </div>

        @if ($orders->hasPages())
            <div class="mt-7">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
