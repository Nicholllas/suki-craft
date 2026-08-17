@extends('layouts.store')

@section('title', 'Pesanan ' . $order->order_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-7 text-center sm:px-10 sm:py-10">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-emerald-600 shadow-sm">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Pesanan berhasil dibuat</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-stone-800 sm:text-4xl">Terima kasih, buketmu segera kami siapkan.</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-stone-600">Simpan nomor pesanan ini untuk memudahkan komunikasi dengan tim Suki Craft.</p>
            <p class="mt-5 font-mono text-lg font-bold tracking-wide text-stone-800">{{ $order->order_number }}</p>
        </div>

        @if (session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                <section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Status pesanan</p><h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">{{ $order->status->label() }}</h2></div><x-status-badge :status="$order->status->label()" /></div>

                    @if (in_array($order->status, [\App\Enums\OrderStatus::PENDING_PAYMENT, \App\Enums\OrderStatus::AWAITING_VERIFICATION], true))
                        <div class="mt-6 border-t border-stone-100 pt-6">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Instruksi pembayaran</p>
                            <h3 class="mt-2 font-serif text-xl font-semibold text-stone-800">Bayar total Rp{{ number_format($order->total, 0, ',', '.') }}</h3>
                            <p class="mt-2 text-sm leading-6 text-stone-500">Gunakan QRIS atau transfer bank, lalu unggah bukti pembayaran agar tim kami dapat memproses pesananmu.</p>
                            @if ($order->status === \App\Enums\OrderStatus::PENDING_PAYMENT)
                                <p class="mt-3 rounded-xl bg-amber-50 px-3 py-2 text-sm font-medium text-amber-800">Selesaikan pembayaran sebelum {{ $order->paymentDeadline()->locale('id')->translatedFormat('d F Y, H.i') }} WIB, yaitu sebelum slot pengiriman dimulai.</p>
                            @endif

                            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                                @if (filled($payment['bank_name']) || filled($payment['bank_account_number']))
                                    <div class="rounded-2xl bg-stone-50 p-4"><p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-400">Transfer bank</p><p class="mt-3 text-sm font-semibold text-stone-800">{{ $payment['bank_name'] ?: 'Bank' }}</p><p class="mt-1 font-mono text-base font-bold text-stone-800">{{ $payment['bank_account_number'] ?: 'Nomor rekening belum diatur' }}</p>@if (filled($payment['bank_account_holder']))<p class="mt-1 text-xs text-stone-500">a.n. {{ $payment['bank_account_holder'] }}</p>@endif</div>
                                @endif
                                <a href="{{ $qrisImageUrl ?? asset($payment['qris_path']) }}" target="_blank" rel="noopener noreferrer" class="group rounded-2xl border border-rose-100 bg-rose-50 p-3 text-center transition hover:border-rose-200 hover:bg-rose-100/60"><img src="{{ $qrisImageUrl ?? asset($payment['qris_path']) }}" alt="QRIS pembayaran Suki Craft" class="mx-auto aspect-square w-full max-w-52 rounded-xl bg-white object-contain p-2"><span class="mt-2 block text-xs font-semibold text-rose-700">{{ $qrisImageUrl ? 'Scan untuk bayar sesuai total pesanan' : 'Ketuk untuk perbesar QRIS' }}</span></a>
                            </div>

                            @if ($order->status === \App\Enums\OrderStatus::AWAITING_VERIFICATION)
                                <div class="mt-5 rounded-2xl bg-amber-50 px-4 py-4 text-sm leading-6 text-amber-800"><p class="font-semibold">Bukti pembayaran sedang diverifikasi.</p><p class="mt-1 text-amber-700">Kami akan memperbarui status pesanan setelah pemeriksaan selesai.</p></div>
                            @else
                                @if ($order->latestPaymentProof?->status === \App\Enums\PaymentProofStatus::REJECTED)
                                    <div class="mt-5 rounded-2xl bg-rose-50 px-4 py-4 text-sm leading-6 text-rose-800"><p class="font-semibold">Bukti pembayaran sebelumnya belum dapat diterima.</p><p class="mt-1 text-rose-700">{{ $order->latestPaymentProof->rejection_reason }}</p></div>
                                @endif

                                <form method="POST" action="{{ route('orders.payment-proofs.store', ['orderNumber' => $order->order_number, 'token' => $order->public_token]) }}" enctype="multipart/form-data" class="mt-5 rounded-2xl border border-stone-200 p-4">
                                    @csrf
                                    <label for="proof" class="text-sm font-semibold text-stone-800">Unggah bukti pembayaran</label>
                                    <input id="proof" name="proof" type="file" accept="image/jpeg,image/png,application/pdf" required class="mt-3 block w-full rounded-xl border border-stone-200 bg-white text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-rose-700 hover:file:bg-rose-100 focus:border-rose-300 focus:ring-rose-200">
                                    <p class="mt-2 text-xs leading-5 text-stone-400">Format JPG, PNG, atau PDF dengan ukuran maksimal 5 MB.</p>
                                    @error('proof')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                                    <button type="submit" class="mt-4 inline-flex h-11 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">Kirim bukti pembayaran</button>
                                </form>
                            @endif

                            @if ($whatsAppUrl)
                                <a href="{{ $whatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 transition hover:text-emerald-800"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2C6.5 2 2 6.44 2 11.9c0 2.15.7 4.15 1.9 5.78L2.68 22l4.48-1.18a10.1 10.1 0 0 0 4.88 1.24h.01c5.53 0 10.03-4.44 10.03-9.9C22.08 6.44 17.58 2 12.04 2Zm0 18.4c-1.55 0-3.07-.41-4.39-1.19l-.32-.19-2.66.7.71-2.57-.21-.34a8.09 8.09 0 0 1-1.25-4.3c0-4.48 3.65-8.13 8.13-8.13 4.48 0 8.12 3.65 8.12 8.13 0 4.48-3.65 8.13-8.13 8.13Zm4.46-6.1c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06a6.55 6.55 0 0 1-1.93-1.18 7.17 7.17 0 0 1-1.32-1.62c-.14-.24-.01-.37.11-.49.11-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.47-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.66 4.13 3.73.58.25 1.03.4 1.38.5.58.18 1.11.15 1.53.09.47-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z" /></svg>Konfirmasi via WhatsApp</a>
                            @else
                                <p class="mt-4 text-xs text-stone-400">Konfirmasi WhatsApp belum tersedia karena nomor admin belum diatur.</p>
                            @endif
                        </div>
                    @endif
                </section>

                <x-order-tracking-timeline :delivery-proof-url="$deliveryProofUrl" :order="$order" />

                <section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                    <h2 class="font-serif text-2xl font-semibold text-stone-800">Detail buket</h2>
                    <div class="mt-5 divide-y divide-stone-100">
                        @foreach ($order->items as $item)
                            <article class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"><div><h3 class="text-sm font-semibold text-stone-800">{{ $item->product_name }}</h3><p class="mt-1 text-xs text-stone-500">{{ $item->variant_label ?? 'Buket pilihan' }} · {{ $item->quantity }}×</p>@if ($item->card_message)<p class="mt-2 text-xs leading-5 text-stone-500"><span class="font-semibold text-stone-600">Pesan kartu:</span> {{ $item->card_message }}</p>@endif</div><p class="shrink-0 text-sm font-semibold text-stone-800">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p></article>
                        @endforeach
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-stone-200 bg-white p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pengiriman</p>
                    <p class="mt-3 text-sm font-semibold text-stone-800">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $order->customer_phone }}</p>
                    <p class="mt-4 text-sm font-semibold text-stone-800">{{ $order->delivery_date->translatedFormat('d F Y') }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p>
                    <p class="mt-4 text-sm leading-6 text-stone-600">{{ $order->delivery_address }}</p>
                    @if ($order->notes)<p class="mt-4 rounded-xl bg-stone-50 px-3 py-2 text-xs leading-5 text-stone-500"><span class="font-semibold text-stone-600">Catatan:</span> {{ $order->notes }}</p>@endif
                </section>

                <section class="rounded-3xl border border-stone-200 bg-white p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Ringkasan pembayaran</p>
                    <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4 text-stone-500"><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-4 text-stone-500"><dt>Pengiriman</dt><dd>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-4 border-t border-stone-100 pt-4 font-semibold text-stone-800"><dt>Total</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div></dl>
                </section>
            </aside>
        </div>

        <div class="mt-8 text-center"><a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 transition hover:text-rose-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19 12H5m5-5-5 5 5 5" /></svg>Kembali ke koleksi buket</a></div>
    </section>
@endsection
