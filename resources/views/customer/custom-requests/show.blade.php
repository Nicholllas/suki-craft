@extends('layouts.store')

@section('title', 'Custom Bouquet ' . $customRequest->request_number . ' | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('customer.custom-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">← Kembali ke permintaan custom</a>

        <div class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Custom Bouquet</p>
            <div class="mt-2 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div><h1 class="font-serif text-3xl font-semibold tracking-tight text-stone-800">{{ $customRequest->custom_category_name ?? $customRequest->customBouquetCategory?->name ?? 'Custom Bouquet' }} #{{ $customRequest->request_number }}</h1><p class="mt-2 text-sm text-stone-500">Dibutuhkan {{ $customRequest->needed_date->locale('id')->translatedFormat('d F Y') }}</p></div>
                <span class="inline-flex w-fit rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">{{ $customRequest->status->label() }}</span>
            </div>
        </div>

        @if (session('success'))<div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>@endif
        @if ($errors->has('custom_request'))<div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first('custom_request') }}</div>@endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="space-y-6">
                @if ($customRequest->status === \App\Enums\CustomRequestStatus::QUOTATION_SENT)
                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 sm:p-7">
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">Penawaran untukmu</p>
                        <h2 class="mt-2 font-serif text-3xl font-semibold text-stone-800">Rp{{ number_format($customRequest->quoted_price, 0, ',', '.') }}</h2>
                        @if ($customRequest->quote_note)<p class="mt-3 text-sm leading-6 text-stone-700">{{ $customRequest->quote_note }}</p>@endif
                        <p class="mt-3 text-xs leading-5 text-stone-600">Berlaku sampai {{ $customRequest->quote_expires_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB.</p>
                        <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                            <form method="POST" action="{{ route('customer.custom-requests.approve', $customRequest) }}">@csrf<button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600 sm:w-auto">Setujui & masukkan keranjang</button></form>
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'request-revision')" class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 bg-white px-5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Minta revisi</button>
                        </div>
                    </section>
                @elseif ($customRequest->status === \App\Enums\CustomRequestStatus::CONVERTED_TO_CART)
                    <section class="rounded-3xl border border-emerald-100 bg-emerald-50 p-5 sm:p-7"><p class="font-semibold text-emerald-800">Penawaran sudah disetujui.</p><p class="mt-2 text-sm leading-6 text-emerald-700">Custom Bouquet ini sudah masuk keranjang dengan harga yang disepakati.</p><a href="{{ route('cart.index') }}" class="mt-4 inline-flex rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Buka keranjang</a></section>
                @elseif ($customRequest->status === \App\Enums\CustomRequestStatus::WAITING_REVIEW)
                    <section class="rounded-3xl border border-amber-100 bg-amber-50 p-5 sm:p-7"><p class="font-semibold text-amber-900">Permintaan sedang ditinjau.</p><p class="mt-2 text-sm leading-6 text-amber-800">Kami sedang mengecek ketersediaan dan kebutuhan rangkaianmu sebelum mengirim penawaran.</p></section>
                @elseif ($customRequest->status === \App\Enums\CustomRequestStatus::REVISION_REQUESTED)
                    <section class="rounded-3xl border border-amber-100 bg-amber-50 p-5 sm:p-7"><p class="font-semibold text-amber-900">Revisi sudah diterima.</p><p class="mt-2 text-sm leading-6 text-amber-800">Admin akan menyiapkan penawaran baru sesuai catatanmu.</p></section>
                @elseif ($customRequest->status === \App\Enums\CustomRequestStatus::REJECTED)
                    <section class="rounded-3xl border border-rose-100 bg-rose-50 p-5 sm:p-7"><p class="font-semibold text-rose-800">Permintaan belum dapat dipenuhi.</p><p class="mt-2 text-sm leading-6 text-rose-700">Lihat catatan admin pada riwayat aktivitas di bawah.</p></section>
                @elseif ($customRequest->status === \App\Enums\CustomRequestStatus::EXPIRED)
                    <section class="rounded-3xl border border-stone-200 bg-stone-50 p-5 sm:p-7"><p class="font-semibold text-stone-800">Penawaran sudah kedaluwarsa.</p><p class="mt-2 text-sm leading-6 text-stone-600">Buat permintaan baru jika kamu masih ingin mewujudkan ide buket ini.</p></section>
                @endif

                @if ($followUpWhatsAppUrl)
                    <a href="{{ $followUpWhatsAppUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 sm:w-auto">Follow up via WhatsApp</a>
                @endif

                <section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Rincian permintaan</p>
                    <div class="mt-5 divide-y divide-stone-100">@foreach ($customRequest->items as $item)<div class="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0"><div><p class="font-semibold text-stone-800">{{ $item->item_name }}</p>@if($item->notes)<p class="mt-1 text-sm text-stone-500">{{ $item->notes }}</p>@endif</div><p class="shrink-0 text-sm font-semibold text-stone-700">{{ $item->quantity }}×</p></div>@endforeach</div>
                    <dl class="mt-6 grid gap-4 border-t border-stone-100 pt-5 text-sm"><div><dt class="text-stone-400">Sumber isi</dt><dd class="mt-1 font-medium text-stone-700">{{ $customRequest->item_source === 'customer_provides' ? 'Saya bawa sendiri' : 'Suki Craft yang belikan' }}</dd></div>@if($customRequest->wrapping_preference)<div><dt class="text-stone-400">Wrapping</dt><dd class="mt-1 font-medium text-stone-700">{{ $customRequest->wrapping_preference }}</dd></div>@endif
@if($customRequest->additional_notes)<div><dt class="text-stone-400">Catatan tambahan</dt><dd class="mt-1 leading-6 text-stone-700">{{ $customRequest->additional_notes }}</dd></div>@endif</dl>
                    @if ($referenceImageUrl)<a href="{{ $referenceImageUrl }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50">Lihat foto referensi</a>@endif
                </section>

                <section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Aktivitas</p><ol class="mt-5 space-y-5">@foreach ($customRequest->histories as $history)<li class="border-l-2 border-rose-100 pl-4"><div class="flex flex-wrap items-center gap-2"><p class="text-sm font-semibold text-stone-800">{{ $history->status->label() }}</p><p class="text-xs text-stone-400">{{ $history->created_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB</p></div>@if($history->note)<p class="mt-1 text-sm leading-6 text-stone-600">{{ $history->note }}</p>@endif</li>@endforeach</ol></section>
            </div>

            <aside class="space-y-6"><section class="rounded-3xl border border-stone-200 bg-white p-5"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Informasi</p><p class="mt-3 text-sm font-semibold text-stone-800">{{ $customRequest->custom_category_name ?? $customRequest->customBouquetCategory?->name ?? $customRequest->product->name }}</p><p class="mt-1 text-sm text-stone-500">Permintaan {{ $customRequest->request_number }}</p></section></aside>
        </div>
    </section>

    @if ($customRequest->status === \App\Enums\CustomRequestStatus::QUOTATION_SENT)
        <x-modal name="request-revision" :show="$errors->has('revision_note') || $errors->has('counter_offer')" maxWidth="md" focusable><form method="POST" action="{{ route('customer.custom-requests.revision', $customRequest) }}">@csrf<div class="px-6 py-6"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Penawaran Custom Bouquet</p><h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Minta revisi atau ajukan harga</h2><div class="mt-5"><label for="counter_offer" class="text-sm font-semibold text-stone-700">Usulan harga <span class="font-normal text-stone-400">(opsional)</span></label><input id="counter_offer" name="counter_offer" type="number" min="1" step="1" inputmode="numeric" value="{{ old('counter_offer') }}" class="mt-2 h-11 w-full rounded-xl border-stone-200 px-4 text-sm" placeholder="Contoh: 300000">@error('counter_offer')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div><textarea name="revision_note" rows="5" required maxlength="1000" class="mt-5 w-full rounded-xl border-stone-200 px-4 py-3 text-sm" placeholder="Ceritakan perubahan yang kamu butuhkan">{{ old('revision_note') }}</textarea>@error('revision_note')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div><div class="flex justify-end gap-3 border-t border-stone-100 bg-stone-50 px-6 py-4"><button type="button" x-on:click="$dispatch('close-modal', 'request-revision')" class="rounded-xl px-4 py-2 text-sm font-semibold text-stone-600">Batal</button><button type="submit" class="rounded-xl bg-rose-500 px-4 py-2 text-sm font-semibold text-white">Kirim revisi</button></div></form></x-modal>
    @endif
@endsection