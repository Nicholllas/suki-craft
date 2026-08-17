@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')

@section('content')
    <div class="max-w-4xl">
        <section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pembayaran</p>
            <h1 class="mt-2 font-serif text-2xl font-semibold text-stone-800">QRIS toko</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">Ganti QRIS bila rekening merchant berubah. Kosongkan payload untuk menonaktifkan QRIS dinamis; gambar QRIS cadangan tetap dapat digunakan.</p>

            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label for="bank_name" class="block text-sm font-semibold text-stone-800">Nama bank</label><input id="bank_name" name="bank_name" value="{{ old('bank_name', $paymentSetting->bank_name) }}" class="mt-2 block w-full rounded-xl border-stone-200 px-4 py-3 text-sm"></div>
                    <div><label for="bank_account_number" class="block text-sm font-semibold text-stone-800">Nomor rekening</label><input id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $paymentSetting->bank_account_number) }}" class="mt-2 block w-full rounded-xl border-stone-200 px-4 py-3 text-sm"></div>
                    <div><label for="bank_account_holder" class="block text-sm font-semibold text-stone-800">Nama pemilik</label><input id="bank_account_holder" name="bank_account_holder" value="{{ old('bank_account_holder', $paymentSetting->bank_account_holder) }}" class="mt-2 block w-full rounded-xl border-stone-200 px-4 py-3 text-sm"></div>
                </div>
                <div>
                    <label for="qris_payload" class="block text-sm font-semibold text-stone-800">Payload QRIS untuk nominal dinamis</label>
                    <textarea id="qris_payload" name="qris_payload" rows="5" class="mt-2 block w-full rounded-xl border-stone-200 px-4 py-3 font-mono text-xs focus:border-rose-300 focus:ring-rose-200" placeholder="000201010211...">{{ old('qris_payload', $paymentSetting->qris_payload) }}</textarea>
                    <p class="mt-2 text-xs text-stone-500">Kosongkan saat mengunggah barcode agar payload dibaca otomatis. Isi manual hanya bila hasil baca perlu dikoreksi.</p>
                    @error('qris_payload')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="qris_image" class="block text-sm font-semibold text-stone-800">Gambar QRIS cadangan</label>
                    <input id="qris_image" name="qris_image" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-stone-200 bg-white text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-rose-700">
                    @error('qris_image')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white hover:bg-rose-600">Simpan QRIS</button>
            </form>
        </section>
    </div>
@endsection
