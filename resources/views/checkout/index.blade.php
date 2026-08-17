@extends('layouts.store')

@section('title', 'Checkout | Suki Craft')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            Kembali ke keranjang
        </a>

        <div class="mt-7 border-b border-stone-200 pb-7">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Satu langkah lagi</p>
            <h1 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Atur pengiriman buketmu</h1>
            <div class="mt-5 flex max-w-md items-center gap-2 text-xs font-semibold">
                <span class="flex items-center gap-2 text-rose-600"><span class="grid h-6 w-6 place-items-center rounded-full bg-rose-500 text-white">1</span>Data pengiriman</span>
                <span class="h-px flex-1 bg-rose-200"></span>
                <span class="flex items-center gap-2 text-stone-400"><span class="grid h-6 w-6 place-items-center rounded-full border border-stone-200 bg-white">2</span>Konfirmasi</span>
            </div>
        </div>

        @if ($errors->has('cart'))
            <div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first('cart') }}</div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_23rem] lg:items-start">
            @csrf

            <div class="space-y-6">
                <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-50 text-sm font-bold text-rose-600">1</span>
                        <div><h2 class="font-serif text-2xl font-semibold text-stone-800">Penerima buket</h2><p class="mt-1 text-sm leading-6 text-stone-500">Kami memakai data ini untuk mengatur pengantaran pesananmu.</p></div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer-name" class="text-sm font-semibold text-stone-700">Nama penerima</label>
                            <input id="customer-name" name="customer_name" type="text" value="{{ old('customer_name', $customer?->name) }}" autocomplete="name" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_name') border-rose-400 @enderror" placeholder="Nama lengkap penerima">
                            @error('customer_name')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer-phone" class="text-sm font-semibold text-stone-700">Nomor WhatsApp</label>
                            <input id="customer-phone" name="customer_phone" type="tel" value="{{ old('customer_phone', $customer?->phone) }}" inputmode="tel" autocomplete="tel" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_phone') border-rose-400 @enderror" placeholder="08xxxxxxxxxx">
                            <p class="mt-2 text-xs text-stone-400">Gunakan format 08xx atau +628xx.</p>
                            @error('customer_phone')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="customer-email" class="text-sm font-semibold text-stone-700">Email <span class="font-normal text-stone-400">(opsional)</span></label>
                            <input id="customer-email" name="customer_email" type="email" value="{{ old('customer_email', $customer?->email) }}" autocomplete="email" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('customer_email') border-rose-400 @enderror" placeholder="email@contoh.com">
                            @error('customer_email')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
                    <div class="flex items-start gap-3">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-rose-50 text-sm font-bold text-rose-600">2</span>
                        <div><h2 class="font-serif text-2xl font-semibold text-stone-800">Jadwal pengiriman</h2><p class="mt-1 text-sm leading-6 text-stone-500">Pilih tanggal serta waktu yang paling nyaman untuk penerima.</p></div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="delivery-date" class="text-sm font-semibold text-stone-700">Tanggal pengiriman</label>
                            <input id="delivery-date" name="delivery_date" type="date" value="{{ old('delivery_date') }}" min="{{ $minimumDeliveryDate }}" required class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 focus:border-rose-300 focus:ring-rose-200 @error('delivery_date') border-rose-400 @enderror">
                            <p class="mt-2 text-xs text-stone-400">Pesanan dapat dikirim mulai {{ \Illuminate\Support\Carbon::parse($minimumDeliveryDate)->translatedFormat('d F Y') }}.</p>
                            @error('delivery_date')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="delivery-time-slot" class="text-sm font-semibold text-stone-700">Estimasi waktu tiba</label>
                            <select id="delivery-time-slot" name="delivery_time_slot" required class="mt-2 h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 focus:border-rose-300 focus:ring-rose-200 @error('delivery_time_slot') border-rose-400 @enderror">
                                <option value="">Pilih waktu pengiriman</option>
                                @foreach ($timeSlots as $value => $label)
                                    <option value="{{ $value }}" @selected(old('delivery_time_slot') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('delivery_time_slot')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="delivery-address" class="text-sm font-semibold text-stone-700">Alamat lengkap pengiriman</label>
                            <textarea id="delivery-address" name="delivery_address" rows="4" required class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('delivery_address') border-rose-400 @enderror" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, dan patokan bila ada">{{ old('delivery_address') }}</textarea>
                            @error('delivery_address')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="text-sm font-semibold text-stone-700">Catatan tambahan <span class="font-normal text-stone-400">(opsional)</span></label>
                            <textarea id="notes" name="notes" rows="3" maxlength="1000" class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200 @error('notes') border-rose-400 @enderror" placeholder="Contoh: Tolong hubungi penerima sebelum tiba.">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside class="sticky bottom-3 rounded-3xl border border-stone-200 bg-white p-5 shadow-xl shadow-stone-900/5 lg:top-24 lg:bottom-auto lg:p-6" x-data="{ showItems: window.innerWidth >= 1024, code: '{{ old('promotion_code', session('checkout.promotion_code')) }}', discount: 0, error: '', loading: false, format(value) { return new Intl.NumberFormat('id-ID').format(value) }, async applyPromotion() { this.loading = true; this.error = ''; try { const response = await fetch('{{ route('checkout.promotions.validate') }}', { method: 'POST', credentials: 'same-origin', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ code: this.code, customer_phone: document.getElementById('customer-phone').value }) }); const payload = await response.text(); const data = payload ? JSON.parse(payload) : {}; if (! response.ok) { this.discount = 0; this.error = data.errors?.promotion_code?.[0] ?? data.message ?? 'Kode promo tidak dapat diterapkan. Silakan coba lagi.'; return; } this.code = data.code; this.discount = data.discount_amount; } catch (error) { this.discount = 0; this.error = 'Kode promo tidak dapat diterapkan. Muat ulang halaman lalu coba kembali.'; } finally { this.loading = false; } } }">
                <div class="flex items-center justify-between gap-4">
                    <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pesananmu</p><h2 class="mt-1 font-serif text-2xl font-semibold text-stone-800">Ringkasan</h2></div>
                    <button type="button" @click="showItems = !showItems" :aria-expanded="showItems" class="rounded-lg px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 lg:hidden"><span x-text="showItems ? 'Tutup' : 'Lihat buket'"></span></button>
                </div>

                <div x-cloak x-show="showItems" x-transition class="mt-5 space-y-4 border-b border-stone-100 pb-5">
                    @foreach ($cart->items as $item)
                        @php($imagePath = $item->product->primary_image?->path ?? $item->product->image)
                        <div class="flex gap-3">
                            <div class="h-16 w-14 shrink-0 overflow-hidden rounded-xl bg-rose-50">
                                @if ($imagePath)
                                    <img src="{{ Storage::url($imagePath) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full place-items-center text-xl text-rose-300">✿</div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1"><p class="truncate text-sm font-semibold text-stone-800">{{ $item->product->name }}</p><p class="mt-0.5 text-xs text-stone-500">{{ $item->variant?->label ?? 'Buket pilihan' }} · {{ $item->quantity }}×</p><p class="mt-1 text-xs font-semibold text-stone-700">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p></div>
                        </div>
                    @endforeach
                </div>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex items-center justify-between text-stone-500"><dt>Subtotal</dt><dd class="font-medium text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd></div>
                    <div class="flex items-center justify-between text-stone-500"><dt>Biaya pengiriman</dt><dd class="font-medium text-stone-800">Rp{{ number_format($deliveryFee, 0, ',', '.') }}</dd></div>
                    <div class="rounded-xl bg-rose-50 p-3"><label for="promotion-code" class="text-xs font-semibold text-stone-700">Kode promo</label><div class="mt-2 flex gap-2"><input id="promotion-code" name="promotion_code" x-model="code" class="min-w-0 flex-1 rounded-lg border-stone-200 px-3 py-2 text-sm uppercase focus:border-rose-300 focus:ring-rose-200" placeholder="PROMO2026"><button type="button" @click="applyPromotion" :disabled="loading" class="rounded-lg bg-stone-800 px-3 text-xs font-semibold text-white disabled:opacity-60" x-text="loading ? '...' : 'Terapkan'"></button></div><p x-show="error" x-text="error" class="mt-2 text-xs text-rose-600"></p>@error('promotion_code')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                    <div x-show="discount > 0" class="flex items-center justify-between text-emerald-600"><dt>Potongan promo</dt><dd class="font-semibold">-Rp<span x-text="format(discount)"></span></dd></div>
                    <div class="flex items-end justify-between border-t border-stone-100 pt-4"><dt class="font-semibold text-stone-800">Total pembayaran</dt><dd class="font-serif text-2xl font-semibold text-stone-800">Rp<span x-text="format({{ $subtotal + $deliveryFee }} - discount)">{{ number_format($subtotal + $deliveryFee, 0, ',', '.') }}</span></dd></div>
                </dl>

                <button type="submit" class="mt-6 inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2">
                    Buat pesanan
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                </button>
                <p class="mt-3 text-center text-xs leading-5 text-stone-400">Detail pembayaran akan ditampilkan setelah pesanan dibuat.</p>
            </aside>
        </form>
    </section>
@endsection
