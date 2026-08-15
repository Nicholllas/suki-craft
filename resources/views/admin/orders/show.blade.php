@extends('layouts.admin')

@section('title', 'Verifikasi ' . $order->order_number)
@section('page-title', 'Verifikasi Pembayaran')

@section('content')
    @php($pendingProof = $order->paymentProofs->first(fn ($proof) => $proof->status === \App\Enums\PaymentProofStatus::PENDING))

    <div>
        <a href="{{ route('admin.payment-verifications.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>Kembali ke verifikasi pembayaran</a>

        <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $order->order_number }}</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Periksa bukti pembayaran</h1><p class="mt-2 text-sm text-stone-500">{{ $order->customer_name }} · Rp{{ number_format($order->total, 0, ',', '.') }}</p></div><x-status-badge :status="$order->status->label()" /></div>

        <div class="mt-7 grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="space-y-6">
                @if ($pendingProof)
                    <x-card padding="p-5 sm:p-7">
                        <div class="flex items-start justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Bukti terbaru</p><h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Menunggu keputusan Anda</h2><p class="mt-1 text-sm text-stone-500">Diunggah {{ $pendingProof->uploaded_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB.</p></div><a href="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $pendingProof]) }}" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-100">Buka penuh</a></div>

                        <div class="mt-6 overflow-hidden rounded-2xl border border-stone-100 bg-stone-50">
                            @if ($pendingProof->is_pdf)
                                <iframe src="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $pendingProof]) }}" title="Bukti pembayaran {{ $order->order_number }}" class="h-[32rem] w-full bg-white"></iframe>
                            @else
                                <a href="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $pendingProof]) }}" target="_blank" rel="noopener noreferrer"><img src="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $pendingProof]) }}" alt="Bukti pembayaran {{ $order->order_number }}" class="max-h-[38rem] w-full object-contain"></a>
                            @endif
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row"><form method="POST" action="{{ route('admin.payment-verifications.payment-proofs.approve', ['order' => $order, 'paymentProof' => $pendingProof]) }}" class="sm:flex-1" data-confirm="Pesanan akan masuk ke tahap persiapan." data-confirm-button="Ya, konfirmasi" data-confirm-title="Konfirmasi pembayaran?">@csrf @method('PATCH')<button class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700">Konfirmasi pembayaran</button></form><button type="button" x-data x-on:click="$dispatch('open-modal', 'reject-payment-proof')" class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 px-5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50 sm:flex-1">Tolak bukti</button></div>
                    </x-card>

                    <x-modal name="reject-payment-proof" focusable>
                        <form method="POST" action="{{ route('admin.payment-verifications.payment-proofs.reject', ['order' => $order, 'paymentProof' => $pendingProof]) }}">
                            @csrf
                            @method('PATCH')
                            <div class="border-b border-rose-100 bg-rose-50/50 px-6 py-6"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Verifikasi pembayaran</p><h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Tolak bukti pembayaran</h2><p class="mt-2 text-sm leading-6 text-stone-500">Jelaskan alasan penolakan agar pelanggan dapat memperbaiki atau mengunggah ulang bukti pembayaran.</p></div><div class="px-6 py-6"><label for="reason" class="block text-sm font-semibold text-stone-700">Alasan penolakan</label><textarea id="reason" name="reason" rows="4" required class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 focus:border-rose-300 focus:ring-rose-200" placeholder="Contoh: Nominal pada bukti pembayaran belum sesuai total pesanan.">{{ old('reason') }}</textarea>@error('reason')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div><div class="grid gap-3 border-t border-stone-100 bg-stone-50/80 px-6 py-4 sm:grid-cols-[1fr_auto]"><button type="button" x-on:click="$dispatch('close-modal', 'reject-payment-proof')" class="inline-flex h-12 items-center justify-center rounded-xl border border-stone-200 bg-white px-5 text-sm font-semibold text-stone-600 hover:bg-stone-100">Batal</button><button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white hover:bg-rose-600">Tolak bukti</button></div>
                        </form>
                    </x-modal>
                @else
                    <x-card padding="p-7"><x-empty-state title="Tidak ada bukti aktif" description="Bukti terbaru untuk pesanan ini sudah diproses atau pelanggan belum mengunggah bukti baru." /></x-card>
                @endif

                <x-card padding="p-5 sm:p-7"><h2 class="font-serif text-2xl font-semibold text-stone-800">Riwayat bukti pembayaran</h2><div class="mt-5 divide-y divide-stone-100">@forelse ($order->paymentProofs as $proof)<div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0"><div><div class="flex flex-wrap items-center gap-2"><p class="text-sm font-semibold text-stone-800">Diunggah {{ $proof->uploaded_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB</p><x-status-badge :status="$proof->status->label()" /></div>@if ($proof->rejection_reason)<p class="mt-2 text-xs leading-5 text-rose-600">Alasan: {{ $proof->rejection_reason }}</p>@endif @if ($proof->verifier)<p class="mt-2 text-xs text-stone-400">Diperiksa oleh {{ $proof->verifier->name }}{{ $proof->verified_at ? ' · '.$proof->verified_at->locale('id')->translatedFormat('d M Y, H.i').' WIB' : '' }}</p>@endif</div><a href="{{ route('admin.payment-verifications.payment-proofs.preview', ['order' => $order, 'paymentProof' => $proof]) }}" target="_blank" rel="noopener noreferrer" class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50">Lihat</a></div>@empty<x-empty-state title="Belum ada bukti" description="Pelanggan belum mengunggah bukti pembayaran." />@endforelse</div></x-card>
            </div>

            <aside class="space-y-6"><x-card padding="p-5"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pelanggan & Pengiriman</p><p class="mt-3 text-sm font-semibold text-stone-800">{{ $order->customer_name }}</p><p class="mt-1 text-sm text-stone-500">{{ $order->customer_phone }}</p><p class="mt-4 text-sm font-semibold text-stone-800">{{ $order->delivery_date->translatedFormat('d F Y') }}</p><p class="mt-1 text-sm text-stone-500">{{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p><p class="mt-4 text-sm leading-6 text-stone-600">{{ $order->delivery_address }}</p>@if ($order->notes)<p class="mt-4 rounded-xl bg-stone-50 px-3 py-2 text-xs leading-5 text-stone-500"><span class="font-semibold text-stone-600">Catatan:</span> {{ $order->notes }}</p>@endif</x-card><x-card padding="p-5"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Ringkasan pesanan</p><div class="mt-4 space-y-3">@foreach ($order->items as $item)<div class="flex items-start justify-between gap-3 text-sm"><div><p class="font-semibold text-stone-800">{{ $item->product_name }}</p><p class="mt-0.5 text-xs text-stone-500">{{ $item->variant_label ?? 'Buket pilihan' }} · {{ $item->quantity }}×</p></div><p class="shrink-0 font-medium text-stone-700">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p></div>@endforeach</div><dl class="mt-5 space-y-3 border-t border-stone-100 pt-4 text-sm"><div class="flex justify-between text-stone-500"><dt>Subtotal</dt><dd>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</dd></div><div class="flex justify-between text-stone-500"><dt>Pengiriman</dt><dd>Rp{{ number_format($order->delivery_fee, 0, ',', '.') }}</dd></div><div class="flex justify-between font-semibold text-stone-800"><dt>Total</dt><dd>Rp{{ number_format($order->total, 0, ',', '.') }}</dd></div></dl></x-card></aside>
        </div>
    </div>
@endsection
