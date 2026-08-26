@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">← Kembali ke riwayat pesanan</a>

        <div class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pesanan</p>
            <div class="mt-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div>
                    <h1 class="font-mono text-xl font-bold tracking-wide text-stone-800">{{ $order->order_number }}</h1>
                    <p class="mt-2 text-sm text-stone-500">Pengiriman {{ $order->delivery_date->translatedFormat('d F Y') }} · {{ config('delivery.time_slots.'.$order->delivery_time_slot.'.label', $order->delivery_time_slot) }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <x-status-badge :status="$order->status->label()" />
                    @if ($whatsAppUrl)
                        <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.5 2 2 6.44 2 11.9c0 2.15.7 4.15 1.9 5.78L2.68 22l4.48-1.18a10.1 10.1 0 0 0 4.88 1.24h.01c5.53 0 10.03-4.44 10.03-9.9C22.08 6.44 17.58 2 12.04 2Zm0 18.4c-1.55 0-3.07-.41-4.39-1.19l-.32-.19-2.66.7.71-2.57-.21-.34a8.09 8.09 0 0 1-1.25-4.3c0-4.48 3.65-8.13 8.13-8.13 4.48 0 8.12 3.65 8.12 8.13 0 4.48-3.65 8.13-8.13 8.13Zm4.46-6.1c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06a6.55 6.55 0 0 1-1.93-1.18 7.17 7.17 0 0 1-1.32-1.62c-.14-.24-.01-.37.11-.49.11-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.66 4.13 3.73.58.25 1.03.4 1.38.5.58.18 1.11.15 1.53.09.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" /></svg>
                            Hubungi admin
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Rincian pesanan</p>
            <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Komponen harga buket</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">Lihat biaya jasa, varian yang dipilih, jumlah, dan subtotal masing-masing buket.</p>

            <div class="mt-5 divide-y divide-stone-100">
                @foreach ($order->itemGroups as $item)
                    <article class="py-4 first:pt-0 last:pb-0">
                        <h3 class="text-sm font-semibold text-stone-800">{{ $item->custom_request_id ? 'Custom Bouquet #'.($item->customRequest?->request_number ?? '') : $item->product_name }}<x-bouquet-size-label :item="$item" /></h3>
                        <x-order-item-price-breakdown :item="$item" />
                        @if ($item->card_message)
                            <p class="mt-2 text-xs leading-5 text-stone-500"><span class="font-semibold text-stone-600">Pesan kartu:</span> {{ $item->card_message }}</p>
                        @endif
                        @if ($item->special_note)
                            <p class="mt-1 text-xs leading-5 text-stone-500"><span class="font-semibold text-stone-600">Catatan:</span> {{ $item->special_note }}</p>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-6 rounded-xl bg-stone-50 px-4 py-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-rose-500">Perhitungan total pembayaran</p>
                <dl class="mt-3 space-y-2 text-sm">
                    <div class="flex justify-between gap-4 text-stone-500"><dt>Subtotal buket</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4 text-stone-500"><dt>Biaya pengiriman</dt><dd>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</dd></div>
                    @if ((float) $order->discount_amount > 0)
                        <div class="flex justify-between gap-4 text-emerald-600"><dt>Potongan promo</dt><dd>-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</dd></div>
                    @endif
                    <div class="flex justify-between gap-4 border-t border-stone-200 pt-3 font-semibold text-stone-800"><dt>Total pembayaran</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div>
                </dl>
                <p class="mt-3 text-xs leading-5 text-stone-500">Total = subtotal buket + biaya pengiriman − potongan promo.</p>
                @if ($order->itemGroups->contains('requires_quote', true))
                    <p class="mt-2 text-xs leading-5 text-stone-500">Untuk buket custom, subtotal mengikuti penawaran harga dari admin.</p>
                @endif
            </div>
        </section>

        @if ($order->status === \App\Enums\OrderStatus::PENDING_PAYMENT)
            <section class="mt-6 rounded-3xl border border-rose-200 bg-rose-50 p-5 sm:flex sm:items-center sm:justify-between sm:gap-6 sm:p-7">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pembayaran belum selesai</p>
                    <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Lanjutkan pembayaran pesananmu</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-600">Lihat instruksi pembayaran dan unggah bukti pembayaran sebelum {{ $order->paymentDeadline()->locale('id')->translatedFormat('d F Y, H.i') }} WIB.</p>
                </div>
                <a href="{{ route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]) }}" class="mt-5 inline-flex shrink-0 items-center justify-center rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 sm:mt-0">Lanjutkan pembayaran</a>
            </section>
        @endif

        <div class="mt-6"><x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" /></div>

        @if ($order->status === \App\Enums\OrderStatus::DELIVERED)
            <section class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Setelah pesanan diterima</p>
                <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Bagaimana buketmu?</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Ulasanmu akan tampil setelah ditinjau oleh tim kami.</p>
                <div class="mt-5 divide-y divide-stone-100">
                    @foreach ($order->itemGroups as $item)
                        <div class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-stone-800">{{ $item->custom_request_id ? 'Custom Bouquet #'.($item->customRequest?->request_number ?? '') : $item->product_name }}<x-bouquet-size-label :item="$item" /></p>
                                @if ($item->variants->isNotEmpty())
                                    <ul class="mt-1 text-sm text-stone-500">
                                        @foreach ($item->variants as $variant)
                                            <li>{{ $variant->variant_label }}@if ($variant->quantity_in_bundle > 1) · {{ $variant->quantity_in_bundle }}×@endif</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            @if ($item->review)
                                <button type="button" disabled class="inline-flex items-center justify-center rounded-xl bg-stone-100 px-4 py-2.5 text-sm font-semibold text-stone-500">Ulasan Terkirim · {{ $item->review->status->label() }}</button>
                            @else
                                <x-review-form :action="route('customer.orders.reviews.store', [$order, $item])" :order-item="$item" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </section>
@endsection
