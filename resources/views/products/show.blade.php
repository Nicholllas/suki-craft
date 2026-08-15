@extends('layouts.store')

@section('title', $product->name . ' | Suki Craft')
@section('description', $product->description ?: 'Buket bunga pilihan dari Suki Craft.')

@section('content')
    @php($imagePath = $product->primary_image?->path ?? $product->image)
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
            Kembali ke koleksi
        </a>

        <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(22rem,.95fr)] lg:gap-16">
            <div x-data="{ activeImage: '{{ $imagePath ? Storage::url($imagePath) : '' }}' }">
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
                            <button @click="activeImage = '{{ Storage::url($image->path) }}'" class="aspect-square overflow-hidden rounded-xl ring-2 ring-transparent transition hover:ring-rose-300 focus:outline-none focus:ring-rose-500">
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
                    <p class="text-sm text-stone-500">Harga mulai dari</p>
                    <p class="mt-1 font-serif text-3xl font-semibold text-stone-800">Rp{{ number_format($product->final_price, 0, ',', '.') }}</p>
                </div>

                @if($product->variants->where('is_active', true)->isNotEmpty())
                    <div class="mt-7" x-data="{ selected: {{ $product->variants->where('is_active', true)->first()->id }} }">
                        <p class="text-sm font-semibold text-stone-800">Pilih ukuran atau varian</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach($product->variants->where('is_active', true) as $variant)
                                <button @click="selected = {{ $variant->id }}" :class="selected === {{ $variant->id }} ? 'border-rose-500 bg-rose-50 text-rose-700' : 'border-stone-200 bg-white text-stone-700 hover:border-rose-200'" class="flex items-center justify-between rounded-xl border px-4 py-3 text-left text-sm transition">
                                    <span class="font-semibold">{{ $variant->label }}</span>
                                    @if($variant->price_adjustment != 0)
                                        <span class="text-xs">{{ $variant->price_adjustment > 0 ? '+' : '-' }}Rp{{ number_format(abs($variant->price_adjustment), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-xs">Harga dasar</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 rounded-2xl bg-rose-50 p-5">
                    <div class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 1.66 5.1H19l-4.33 3.15 1.66 5.1L12 13.2l-4.33 3.15 1.66-5.1L5 8.1h5.34L12 3Z" /></svg>
                        <div><p class="text-sm font-semibold text-stone-800">Dirangkai saat pesanan dibuat</p><p class="mt-1 text-sm leading-6 text-stone-600">Bunga dipilih sesuai kesegaran dan dirangkai dengan kartu ucapan gratis untuk setiap pesanan.</p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
