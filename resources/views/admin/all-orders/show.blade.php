@extends('layouts.admin')

@section('title', 'Pesanan ' . $order->order_number)
@section('page-title', 'Detail Pesanan')

@section('content')
    <div>
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>Kembali ke semua pesanan</a>

        <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="font-mono text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $order->order_number }}</p>
                <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Detail pesanan</h1>
                <p class="mt-2 text-sm text-stone-500">Dibuat {{ $order->created_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB oleh {{ $order->customer_name }}.</p>
            </div>
            <x-status-badge :status="$order->status->label()" />
        </div>

        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                <x-card padding="p-5 sm:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Item pesanan</p>
                            <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Buket pilihan pelanggan</h2>
                        </div>
                        <p class="rounded-xl bg-stone-50 px-3 py-2 text-xs font-semibold text-stone-600">{{ $order->itemGroups->sum('bundle_quantity') }} buket</p>
                    </div>

                    <div class="mt-6 divide-y divide-stone-100">
                        @foreach ($order->itemGroups as $item)
                            <div class="py-5 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-base font-semibold text-stone-800">{{ $item->product_name }}</p>
                                        <p class="mt-1 text-sm text-stone-500">{{ $item->bundle_quantity }} buket</p>
                                        @if ($item->variants->isNotEmpty())<ul class="mt-1 text-sm text-stone-500">@foreach ($item->variants as $variant)<li>{{ $variant->variant_label }}@if ($variant->quantity_in_bundle > 1) · {{ $variant->quantity_in_bundle }}×@endif</li>@endforeach</ul>@endif
                                    </div>
                                    <p class="shrink-0 text-sm font-semibold text-stone-800">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                                @if ($item->card_message || $item->special_note)
                                    <div class="mt-4 grid gap-2 rounded-2xl bg-stone-50 p-3 text-xs leading-5 text-stone-600 sm:grid-cols-2">
                                        @if ($item->card_message)<p><span class="font-semibold text-stone-700">Kartu:</span> {{ $item->card_message }}</p>@endif
                                        @if ($item->special_note)<p><span class="font-semibold text-stone-700">Untuk florist:</span> {{ $item->special_note }}</p>@endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>

                <x-card padding="p-5 sm:p-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pembayaran</p>
                        <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Riwayat bukti pembayaran</h2>
                    </div>

                    <div class="mt-6 divide-y divide-stone-100">
                        @forelse ($order->paymentProofs as $proof)
                            <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2"><p class="text-sm font-semibold text-stone-800">Diunggah {{ $proof->uploaded_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB</p><x-status-badge :status="$proof->status->label()" /></div>
                                    @if ($proof->rejection_reason)<p class="mt-2 text-xs leading-5 text-rose-600"><span class="font-semibold">Alasan penolakan:</span> {{ $proof->rejection_reason }}</p>@endif
                                    @if ($proof->verifier)<p class="mt-2 text-xs text-stone-400">Diperiksa oleh {{ $proof->verifier->name }}{{ $proof->verified_at ? ' · '.$proof->verified_at->locale('id')->translatedFormat('d M Y, H.i').' WIB' : '' }}</p>@endif
                                </div>
                                <a href="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $proof]) }}" target="_blank" rel="noopener noreferrer" class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Lihat</a>
                            </div>
                        @empty
                            <x-empty-state title="Belum ada bukti pembayaran" description="Pelanggan belum mengunggah bukti pembayaran untuk pesanan ini." />
                        @endforelse
                    </div>
                </x-card>

                <x-card padding="p-5 sm:p-7">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Aktivitas</p>
                        <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Timeline pesanan</h2>
                    </div>

                    <ol class="mt-6 space-y-0">
                        @forelse ($order->statusHistories as $history)
                            <li class="relative grid grid-cols-[1.25rem_minmax(0,1fr)] gap-3 pb-6 last:pb-0">
                                <span class="relative flex justify-center"><span class="mt-1.5 h-3 w-3 rounded-full bg-rose-500 ring-4 ring-rose-100"></span>@if (! $loop->last)<span class="absolute bottom-0 top-5 w-px bg-stone-200"></span>@endif</span>
                                <div class="pb-0.5"><div class="flex flex-wrap items-center gap-2"><x-status-badge :status="$history->status->label()" /><p class="text-xs text-stone-400">{{ $history->created_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB</p></div><p class="mt-2 text-sm leading-6 text-stone-600">{{ $history->note ?: 'Status pesanan diperbarui.' }}</p><p class="mt-1 text-xs font-medium text-stone-400">{{ $history->changedBy?->name ?? 'Pelanggan / sistem' }}</p></div>
                            </li>
                        @empty
                            <x-empty-state title="Belum ada riwayat" description="Perubahan status pesanan akan dicatat di sini." />
                        @endforelse
                    </ol>
                </x-card>
            </div>

            <aside class="space-y-6">
                <x-card padding="p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Aksi cepat</p>
                    <div class="mt-4 space-y-3">
                        @if ($order->status === \App\Enums\OrderStatus::AWAITING_VERIFICATION)
                            <a href="{{ route('admin.payment-verifications.show', $order) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-amber-500 px-4 text-sm font-semibold text-white transition hover:bg-amber-600">Verifikasi pembayaran</a>
                        @elseif (in_array($order->status, [\App\Enums\OrderStatus::PAYMENT_CONFIRMED, \App\Enums\OrderStatus::PROCESSING, \App\Enums\OrderStatus::OUT_FOR_DELIVERY], true))
                            <a href="{{ route('admin.deliveries.index', ['date' => $order->delivery_date->toDateString()]) }}" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-violet-600 px-4 text-sm font-semibold text-white transition hover:bg-violet-700">Kelola pengiriman</a>
                        @elseif ($order->status === \App\Enums\OrderStatus::PENDING_PAYMENT)
                            <p class="rounded-xl bg-amber-50 px-3 py-3 text-xs leading-5 text-amber-800">Menunggu pelanggan mengunggah bukti pembayaran.</p>
                        @elseif ($order->status === \App\Enums\OrderStatus::DELIVERED)
                            <p class="rounded-xl bg-emerald-50 px-3 py-3 text-xs leading-5 text-emerald-800">Pesanan telah selesai dikirim.</p>
                        @else
                            <p class="rounded-xl bg-rose-50 px-3 py-3 text-xs leading-5 text-rose-800">Pesanan telah dibatalkan.</p>
                        @endif
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'override-order-status')" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-rose-200 bg-white px-4 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Ubah status manual</button>
                    </div>
                </x-card>

                <x-card padding="p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pelanggan</p>
                    <p class="mt-3 text-sm font-semibold text-stone-800">{{ $order->customer_name }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ $order->customer_phone }}</p>
                    @if ($order->customer_email)<p class="mt-1 break-all text-sm text-stone-500">{{ $order->customer_email }}</p>@endif
                    <div class="mt-5 border-t border-stone-100 pt-5"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Alamat pengiriman</p><p class="mt-3 text-sm leading-6 text-stone-600">{{ $order->delivery_address }}</p></div>
                    @if ($order->notes)<p class="mt-4 rounded-xl bg-stone-50 px-3 py-3 text-xs leading-5 text-stone-600"><span class="font-semibold text-stone-700">Catatan pesanan:</span> {{ $order->notes }}</p>@endif
                </x-card>

                <x-card padding="p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pengiriman</p>
                    <p class="mt-3 text-sm font-semibold text-stone-800">{{ $order->delivery_date->locale('id')->translatedFormat('d F Y') }}</p>
                    <p class="mt-1 text-sm text-stone-500">{{ config('delivery.time_slots.'.$order->delivery_time_slot.'.label', $order->delivery_time_slot) }}</p>
                    <div class="mt-5 border-t border-stone-100 pt-5"><p class="text-xs font-semibold text-stone-400">Kurir</p><p class="mt-1 text-sm font-semibold text-stone-800">{{ $order->courier?->name ?? 'Belum ditugaskan' }}</p>@if ($order->courier)<p class="mt-1 text-sm text-stone-500">{{ $order->courier->phone }}</p>@endif</div>
                    @if ($order->delivered_at)<div class="mt-5 border-t border-stone-100 pt-5"><p class="text-xs font-semibold text-stone-400">Terkirim</p><p class="mt-1 text-sm font-semibold text-stone-800">{{ $order->delivered_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p></div>@endif
                    @if ($order->cancellation_reason)<p class="mt-4 rounded-xl bg-rose-50 px-3 py-3 text-xs leading-5 text-rose-700"><span class="font-semibold">Alasan pembatalan:</span> {{ $order->cancellation_reason }}</p>@endif
                    @if ($order->delivery_proof_path)<a href="{{ route('admin.orders.delivery-proof', $order) }}" target="_blank" rel="noopener noreferrer" class="mt-5 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-50"><img src="{{ route('admin.orders.delivery-proof', $order) }}" alt="Bukti pengiriman pesanan {{ $order->order_number }}" class="max-h-64 w-full object-contain"><span class="block border-t border-stone-100 bg-white px-3 py-2 text-xs font-semibold text-rose-600">Buka bukti pengiriman</span></a>@endif
                </x-card>

                <x-card padding="p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Ringkasan biaya</p>
                    <dl class="mt-4 space-y-3 text-sm"><div class="flex justify-between gap-4 text-stone-500"><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-4 text-stone-500"><dt>Pengiriman</dt><dd>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-4 border-t border-stone-100 pt-3 font-semibold text-stone-800"><dt>Total</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div></dl>
                </x-card>
            </aside>
        </div>
    </div>

    <x-modal name="override-order-status" :show="$errors->isNotEmpty()" maxWidth="md" focusable>
        <form method="POST" action="{{ route('admin.orders.update-status', $order) }}">
            @csrf
            @method('PATCH')
            <div class="border-b border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50/60 px-6 py-6 sm:px-7"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Kasus khusus</p><h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Ubah status manual</h2><p class="mt-2 text-sm leading-6 text-stone-500">Gunakan hanya untuk koreksi atau keadaan di luar alur operasional normal. Alasan wajib dicatat pada timeline.</p></div>
            <div class="space-y-5 px-6 py-6 sm:px-7">
                <div><label for="status" class="block text-sm font-semibold text-stone-700">Status baru</label><select id="status" name="status" required class="mt-2 h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 focus:border-rose-300 focus:ring-rose-200">@foreach ($statusOptions as $status)<option value="{{ $status->value }}" @selected(old('status', $order->status->value) === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="reason" class="block text-sm font-semibold text-stone-700">Catatan atau alasan</label><textarea id="reason" name="reason" rows="5" required class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200" placeholder="Jelaskan alasan perubahan status ini.">{{ old('reason') }}</textarea><p class="mt-2 text-xs text-stone-400">Catatan ini akan tercatat pada timeline pesanan.</p>@error('reason')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div>
            </div>
            <div class="grid gap-3 border-t border-stone-100 bg-stone-50/80 px-6 py-4 sm:grid-cols-[1fr_auto] sm:px-7"><button type="button" x-on:click="$dispatch('close-modal', 'override-order-status')" class="inline-flex h-12 items-center justify-center rounded-xl border border-stone-200 bg-white px-5 text-sm font-semibold text-stone-600 transition hover:bg-stone-100">Batal</button><button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:bg-rose-600">Simpan perubahan</button></div>
        </form>
    </x-modal>
@endsection
