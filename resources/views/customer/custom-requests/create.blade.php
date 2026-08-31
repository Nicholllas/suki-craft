@extends('layouts.store')

@section('title', app()->isLocale('en') ? 'Custom Bouquet Request | Suki Craft' : 'Request Buket Spesifik | Suki Craft')

@section('content')
    @if (app()->isLocale('en'))
        @include('customer.custom-requests.create-en')
    @else
    @php($selectedCategoryId = old('custom_bouquet_category_id', $customBouquetCategories->firstWhere('slug', $selectedCategorySlug)?->id))

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">← Kembali ke koleksi</a>

        <div class="mt-7 max-w-3xl">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Request Buket Spesifik</p>
            <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Wujudkan buket sesuai idemu</h1>
            <p class="mt-4 text-base leading-7 text-stone-600">Pilih jenis buket, ceritakan isi dan konsepnya, lalu florist akan mengirim penawaran harga sebelum kamu melanjutkan ke pembayaran.</p>
        </div>

        @auth('customer')
            <form method="POST" action="{{ route('custom-requests.store') }}" enctype="multipart/form-data" x-data="{ items: @js(old('items', [['name' => '', 'quantity' => 1, 'notes' => '']])), addItem() { if (this.items.length < 10) this.items.push({ name: '', quantity: 1, notes: '' }); }, removeItem(index) { if (this.items.length > 1) this.items.splice(index, 1); } }" class="mt-8 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">Periksa kembali detail permintaan yang ditandai.</div>
                @endif

                <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-serif text-2xl font-semibold text-stone-800">Jenis buket dan isinya</h2>
                    <div class="mt-5">
                        <label for="custom_bouquet_category_id" class="text-sm font-semibold text-stone-700">Kategori buket</label>
                        <select id="custom_bouquet_category_id" name="custom_bouquet_category_id" required class="mt-2 h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm focus:border-rose-300 focus:ring-rose-200">
                            <option value="">Pilih jenis buket</option>
                            @foreach ($customBouquetCategories as $category)
                                <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('custom_bouquet_category_id')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($customBouquetCategories as $category)
                                <p class="rounded-xl bg-stone-50 px-3 py-2 text-xs leading-5 text-stone-600"><span class="font-semibold text-stone-700">{{ $category->name }}</span>@if ($category->description) · {{ $category->description }}@endif @if ($category->quote_threshold) <span class="text-rose-600">{{ $category->quantity_label }} {{ $category->quote_threshold }} ke atas perlu penawaran.</span>@endif</p>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 flex items-start justify-between gap-4">
                        <div><h3 class="text-sm font-semibold text-stone-700">Isi buket yang diinginkan</h3><p class="mt-1 text-sm leading-6 text-stone-500">Tambahkan bunga, snack, uang, boneka, atau isi lain yang ingin dirangkai.</p></div>
                        <button type="button" @click="addItem" :disabled="items.length >= 10" class="shrink-0 rounded-xl bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-600 disabled:opacity-50">+ Tambah isi</button>
                    </div>
                    @error('items')<p class="mt-3 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                    <div class="mt-5 space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="rounded-2xl border border-stone-100 bg-stone-50 p-4">
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_7rem_auto]">
                                    <div><label class="text-xs font-semibold text-stone-600">Nama isi</label><input :name="`items[${index}][name]`" x-model="item.name" required maxlength="255" class="mt-1.5 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm" placeholder="Contoh: Cokelat Kinder"></div>
                                    <div><label class="text-xs font-semibold text-stone-600">Jumlah</label><input :name="`items[${index}][quantity]`" x-model="item.quantity" required min="1" max="99" type="number" inputmode="numeric" class="mt-1.5 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm"></div>
                                    <button type="button" @click="removeItem(index)" :disabled="items.length === 1" class="self-end rounded-xl px-3 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-100 disabled:text-stone-300">Hapus</button>
                                </div>
                                <div class="mt-3"><label class="text-xs font-semibold text-stone-600">Catatan isi <span class="font-normal text-stone-400">(opsional)</span></label><textarea :name="`items[${index}][notes]`" x-model="item.notes" rows="2" maxlength="1000" class="mt-1.5 w-full rounded-xl border-stone-200 bg-white px-3 py-2 text-sm" placeholder="Contoh: warna biru pastel"></textarea></div>
                            </div>
                        </template>
                    </div>
                </section>

                <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-serif text-2xl font-semibold text-stone-800">Preferensi rangkaian</h2>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2"><p class="text-sm font-semibold text-stone-700">Rentang anggaran</p><div class="mt-3 grid gap-2 sm:grid-cols-2">@foreach (\App\Services\CustomRequestService::budgetRanges() as $value => $range)<label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 px-3 py-3 text-sm text-stone-700"><input type="radio" name="budget_range" value="{{ $value }}" @checked(old('budget_range') === $value) required class="border-stone-300 text-rose-500 focus:ring-rose-300"><span>{{ $range['label'] }}</span></label>@endforeach</div>@error('budget_range')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div>
                        <div><label for="wrapping_preference" class="text-sm font-semibold text-stone-700">Preferensi wrapping <span class="font-normal text-stone-400">(opsional)</span></label><input id="wrapping_preference" name="wrapping_preference" value="{{ old('wrapping_preference') }}" maxlength="255" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm" placeholder="Contoh: putih, pink, elegan"></div>
                        <div><label for="needed_date" class="text-sm font-semibold text-stone-700">Dibutuhkan tanggal</label><input id="needed_date" name="needed_date" type="date" min="{{ now('Asia/Jakarta')->toDateString() }}" value="{{ old('needed_date') }}" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm">@error('needed_date')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><p class="text-sm font-semibold text-stone-700">Sumber isi buket</p><div class="mt-3 grid gap-2 sm:grid-cols-2"><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 px-3 py-3 text-sm text-stone-700"><input type="radio" name="item_source" value="sukicraft_purchases" @checked(old('item_source', 'sukicraft_purchases') === 'sukicraft_purchases') class="border-stone-300 text-rose-500"><span>Suki Craft yang belikan</span></label><label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 px-3 py-3 text-sm text-stone-700"><input type="radio" name="item_source" value="customer_provides" @checked(old('item_source') === 'customer_provides') class="border-stone-300 text-rose-500"><span>Saya bawa sendiri</span></label></div>@error('item_source')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror</div>
                        <div class="sm:col-span-2"><label for="additional_notes" class="text-sm font-semibold text-stone-700">Catatan tambahan <span class="font-normal text-stone-400">(opsional)</span></label><textarea id="additional_notes" name="additional_notes" rows="4" maxlength="1000" class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm" placeholder="Contoh: nuansa warna, ukuran, atau acara khusus">{{ old('additional_notes') }}</textarea></div>
                        <div class="sm:col-span-2"><label for="reference_image" class="text-sm font-semibold text-stone-700">Foto referensi <span class="font-normal text-stone-400">(opsional)</span></label><input id="reference_image" name="reference_image" type="file" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full rounded-xl border border-dashed border-rose-200 bg-rose-50/50 px-4 py-4 text-sm text-stone-600">@error('reference_image')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror<p class="mt-2 text-xs text-stone-400">JPG, PNG, atau WebP maksimal 2 MB.</p></div>
                    </div>
                </section>

                <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 sm:w-auto">Kirim request buket</button>
            </form>
        @else
            <div class="mt-8 rounded-3xl border border-rose-100 bg-rose-50 p-5"><h2 class="font-serif text-2xl font-semibold text-stone-800">Siap mewujudkan idemu?</h2><p class="mt-2 text-sm leading-6 text-stone-600">Masuk atau daftar terlebih dahulu untuk mengirim request buket dan menerima penawaran dari admin.</p><a href="{{ route('customer.login') }}" class="mt-5 inline-flex h-11 items-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600">Masuk untuk mulai custom</a></div>
        @endauth
    </section>
    @endif
@endsection
