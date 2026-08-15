@extends('layouts.store')

@section('title', $product->name . ' | Suki Craft')
@section('description', $product->description ?: 'Buket bunga pilihan dari Suki Craft.')

@section('content')
    @php
        $imagePath = $product->primary_image?->path ?? $product->image;
        $activeVariants = $product->variants->where('is_active', true)->values();
        $variantPrices = $activeVariants->map(fn ($variant) => [
            'id' => $variant->id,
            'price' => (float) $product->base_price + (float) $variant->price_adjustment,
        ])->values();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8" x-data="{
        activeImage: @js($imagePath ? Storage::url($imagePath) : null),
        basePrice: {{ (float) $product->base_price }},
        cardMessage: '',
        quantity: 1,
        selectedVariant: @js($activeVariants->first()?->id),
        specialNote: '',
        submitting: false,
        toast: '',
        toastType: 'success',
        variants: @js($variantPrices),
        formatPrice(value) { return new Intl.NumberFormat('id-ID').format(value); },
        selectedPrice() { return this.variants.find((variant) => variant.id === this.selectedVariant)?.price ?? this.basePrice; },
        async addToCart(event) {
            this.submitting = true;
            this.toast = '';

            try {
                const response = await fetch(event.target.action, {
                    body: new FormData(event.target),
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    method: 'POST',
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Keranjang belum dapat diperbarui.');
                }

                this.toast = data.message;
                this.toastType = 'success';
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: data.count } }));
            } catch (error) {
                this.toast = error.message || 'Keranjang belum dapat diperbarui. Silakan coba lagi.';
                this.toastType = 'error';
            } finally {
                this.submitting = false;
            }
        },
    }">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            Kembali ke koleksi
        </a>

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,.95fr)] lg:gap-16">
            <div>
                <div class="aspect-[4/5] overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 via-orange-50 to-amber-100">
                    <template x-if="activeImage">
                        <img :src="activeImage" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    </template>
                    <template x-if="!activeImage">
                        <div class="grid h-full place-items-center text-7xl text-rose-300">✿</div>
                    </template>
                </div>

                @if($product->images->count() > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-5">
                        @foreach($product->images as $image)
                            <button type="button" @click="activeImage = '{{ Storage::url($image->path) }}'" :class="activeImage === '{{ Storage::url($image->path) }}' ? 'ring-rose-500' : 'ring-transparent hover:ring-rose-200'" class="aspect-square overflow-hidden rounded-xl ring-2 transition focus:outline-none focus:ring-rose-500">
                                <img src="{{ Storage::url($image->path) }}" alt="Tampilan {{ $loop->iteration }} {{ $product->name }}" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="self-center">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ $product->category->name }}</p>
                <h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-5 text-base leading-7 text-stone-600">{{ $product->description ?: 'Rangkaian bunga segar yang dipilih dan dirangkai dengan teliti untuk membuat momen Anda terasa lebih berarti.' }}</p>

                <div class="mt-7 border-y border-stone-200 py-5">
                    <p class="text-sm text-stone-500">Harga untuk pilihanmu</p>
                    <p class="mt-1 font-serif text-3xl font-semibold text-stone-800">Rp<span x-text="formatPrice(selectedPrice())"></span></p>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" class="mt-7 space-y-7" @submit.prevent="addToCart($event)">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($activeVariants->isNotEmpty())
                        <input type="hidden" name="variant_id" :value="selectedVariant">
                        <fieldset>
                            <legend class="text-sm font-semibold text-stone-800">Pilih ukuran atau varian</legend>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                @foreach($activeVariants as $variant)
                                    <button type="button" @click="selectedVariant = {{ $variant->id }}" :class="selectedVariant === {{ $variant->id }} ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-stone-200 bg-white text-stone-700 hover:border-rose-200'" class="flex items-center justify-between rounded-xl border px-4 py-3 text-left text-sm transition">
                                        <span class="font-semibold">{{ $variant->label }}</span>
                                        <span class="text-xs">{{ $variant->price_adjustment > 0 ? '+' : ($variant->price_adjustment < 0 ? '-' : '') }}Rp{{ number_format(abs($variant->price_adjustment), 0, ',', '.') }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <div>
                        <div class="flex items-end justify-between gap-4"><label for="card-message" class="text-sm font-semibold text-stone-800">Pesan kartu ucapan <span class="font-normal text-stone-400">(opsional)</span></label><span class="text-xs text-stone-400"><span x-text="cardMessage.length"></span>/200</span></div>
                        <textarea id="card-message" name="card_message" x-model="cardMessage" maxlength="200" rows="3" placeholder="Contoh: Selamat ulang tahun, semoga harimu seindah bunga ini." class="mt-3 w-full rounded-xl border-stone-200 bg-white px-4 py-3 text-sm leading-6 text-stone-700 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200"></textarea>
                    </div>

                    <div>
                        <div class="flex items-end justify-between gap-4"><label for="special-note" class="text-sm font-semibold text-stone-800">Catatan untuk florist <span class="font-normal text-stone-400">(opsional)</span></label><span class="text-xs text-stone-400"><span x-text="specialNote.length"></span>/300</span></div>
                        <textarea id="special-note" name="special_note" x-model="specialNote" maxlength="300" rows="2" placeholder="Contoh: Mohon dominan warna pastel dan dikirim sore hari." class="mt-3 w-full rounded-xl border-stone-200 bg-white px-4 py-3 text-sm leading-6 text-stone-700 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200"></textarea>
                    </div>

                    <div class="flex items-center justify-between gap-4 rounded-2xl bg-stone-50 p-4">
                        <div><p class="text-sm font-semibold text-stone-800">Jumlah buket</p><p class="mt-0.5 text-xs text-stone-500">Minimal pembelian satu buket.</p></div>
                        <div class="flex items-center rounded-xl border border-stone-200 bg-white">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="grid h-10 w-10 place-items-center text-lg text-stone-500 transition hover:text-rose-600" aria-label="Kurangi jumlah">−</button>
                            <input type="number" name="quantity" x-model.number="quantity" min="1" max="99" class="h-10 w-10 border-0 bg-transparent p-0 text-center text-sm font-semibold text-stone-800 focus:ring-0">
                            <button type="button" @click="quantity = Math.min(99, quantity + 1)" class="grid h-10 w-10 place-items-center text-lg text-stone-500 transition hover:text-rose-600" aria-label="Tambah jumlah">+</button>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting" class="inline-flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-rose-500 px-6 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 disabled:cursor-wait disabled:opacity-70">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h1.15c.67 0 1.25.47 1.39 1.13l.22 1.04m0 0 1.2 5.67a1.5 1.5 0 0 0 1.47 1.19h7.59a1.5 1.5 0 0 0 1.44-1.08l1.06-3.68a.75.75 0 0 0-.72-.96H6.51" /></svg>
                        <span x-text="submitting ? 'Menambahkan...' : 'Tambah ke keranjang'"></span>
                    </button>
                    <p x-cloak x-show="toast" x-text="toast" x-transition :class="toastType === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'" class="rounded-xl px-4 py-3 text-center text-sm font-medium" role="status"></p>
                </form>

                <div class="mt-7 rounded-2xl bg-rose-50 p-5"><div class="flex gap-3"><svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.66 5.1H19l-4.33 3.15 1.66 5.1L12 13.2l-4.33 3.15 1.66-5.1L5 8.1h5.34L12 3Z" /></svg><div><p class="text-sm font-semibold text-stone-800">Dirangkai saat pesanan dibuat</p><p class="mt-1 text-sm leading-6 text-stone-600">Bunga dipilih sesuai kesegaran dan setiap pesanan mendapat kartu ucapan gratis.</p></div></div></div>
            </div>
        </div>
    </section>
@endsection
