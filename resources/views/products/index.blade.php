@extends('layouts.store')

@section('title', 'Koleksi Buket | Suki Craft')
@section('description', 'Temukan buket bunga pilihan dari Suki Craft untuk setiap momen istimewa.')

@section('content')
    <section class="overflow-hidden bg-rose-50/70">
        <div class="mx-auto max-w-7xl px-4 pb-12 pt-14 sm:px-6 sm:pb-16 sm:pt-20 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-rose-500">Koleksi Suki Craft</p>
                <h1 class="mt-4 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Buket untuk setiap pesan yang ingin disampaikan.</h1>
                <p class="mx-auto mt-5 max-w-2xl text-base leading-7 text-stone-600">Temukan rangkaian bunga yang dibuat untuk merayakan momen kecil, hari istimewa, dan segala rasa di antaranya.</p>
            </div>

            <form method="GET" action="{{ route('products.index') }}" class="mx-auto mt-9 max-w-3xl rounded-2xl bg-white p-2 shadow-xl shadow-rose-950/5 ring-1 ring-stone-900/5 sm:flex sm:items-center">
                <label for="search" class="sr-only">Cari buket</label>
                <div class="flex min-w-0 flex-1 items-center gap-3 px-3">
                    <svg class="h-5 w-5 shrink-0 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path stroke-linecap="round" d="m16 16 4.25 4.25" /></svg>
                    <input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari buket, momen, atau jenis bunga..." class="h-12 w-full border-0 bg-transparent text-sm text-stone-800 placeholder:text-stone-400 focus:ring-0">
                </div>
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
                <button class="mt-2 inline-flex h-12 w-full items-center justify-center rounded-xl bg-stone-800 px-6 text-sm font-semibold text-white transition hover:bg-rose-600 sm:mt-0 sm:w-auto">Cari buket</button>
            </form>

            <div class="mt-7 flex flex-wrap justify-center gap-2" aria-label="Kategori buket">
                <a href="{{ route('products.index', request()->filled('search') ? ['search' => request('search')] : []) }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->filled('category') ? 'bg-white text-stone-600 ring-1 ring-stone-200 hover:border-rose-200 hover:text-rose-600' : 'bg-rose-500 text-white shadow-sm shadow-rose-200' }}">Semua buket</a>
                @foreach($categories as $category)
                    <a href="{{ route('products.index', array_filter(['category' => $category->slug, 'search' => request('search')])) }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request('category') === $category->slug ? 'bg-rose-500 text-white shadow-sm shadow-rose-200' : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:border-rose-200 hover:text-rose-600' }}">{{ $category->name }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 border-b border-stone-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-rose-600">Pilihan untukmu</p>
                <h2 class="mt-1 font-serif text-3xl font-semibold tracking-tight text-stone-800">{{ request()->filled('category') ? optional($categories->firstWhere('slug', request('category')))->name ?? 'Hasil pencarian' : 'Semua koleksi' }}</h2>
            </div>
            <p class="text-sm text-stone-500"><span class="font-semibold text-stone-700">{{ $products->total() }}</span> buket ditemukan</p>
        </div>

        @forelse($products as $product)
            @if($loop->first)
                <div class="mt-8 grid gap-x-5 gap-y-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @endif

            @php($imagePath = $product->primary_image?->path ?? $product->image)
            <article class="group overflow-hidden rounded-2xl bg-white ring-1 ring-stone-200/80 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-stone-900/10">
                <a href="{{ route('products.show', $product->slug) }}" class="block">
                    <div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-br from-rose-100 via-orange-50 to-amber-100">
                        @if($imagePath)
                            <img src="{{ Storage::url($imagePath) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <div class="absolute inset-0 grid place-items-center">
                                <svg class="h-20 w-20 text-rose-300/80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21V10.5m0 0c-2.5 0-4.5-1.79-4.5-4 2.5 0 4.5 1.79 4.5 4Zm0 0c2.5 0 4.5-1.79 4.5-4-2.5 0-4.5 1.79-4.5 4Zm0 10.5c0-3.5 1.67-6.25 4.5-7.75M12 21c0-3.5-1.67-6.25-4.5-7.75" /><path stroke-linecap="round" d="M6.25 20.75h11.5" /></svg>
                            </div>
                        @endif

                        @if($product->is_featured)
                            <span class="absolute left-3 top-3 rounded-full bg-white/95 px-3 py-1.5 text-[10px] font-bold uppercase tracking-[0.14em] text-rose-600 shadow-sm backdrop-blur">Pilihan spesial</span>
                        @endif
                    </div>
                </a>

                <div class="p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-rose-500">{{ $product->category->name }}</p>
                    <h3 class="mt-2 font-serif text-xl font-semibold text-stone-800"><a href="{{ route('products.show', $product->slug) }}" class="transition hover:text-rose-600">{{ $product->name }}</a></h3>
                    <p class="mt-2 line-clamp-2 text-sm leading-6 text-stone-500">{{ $product->description ?: 'Rangkaian bunga segar yang diracik dengan perhatian untuk momen spesial Anda.' }}</p>

                    <div class="mt-5 flex items-end justify-between gap-3">
                        <div>
                            <p class="text-xs text-stone-400">Mulai dari</p>
                            <p class="mt-0.5 text-lg font-semibold text-stone-800">Rp{{ number_format($product->final_price, 0, ',', '.') }}</p>
                        </div>
                        <a href="{{ route('products.show', $product->slug) }}" class="grid h-10 w-10 place-items-center rounded-full bg-rose-50 text-rose-600 transition group-hover:bg-rose-500 group-hover:text-white" aria-label="Lihat {{ $product->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5 5 5-5 5" /></svg>
                        </a>
                    </div>
                </div>
            </article>

            @if($loop->last)
                </div>
            @endif
        @empty
            <div class="mt-8 rounded-3xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-16 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-2xl text-rose-400 shadow-sm">✿</div>
                <h3 class="mt-5 font-serif text-2xl font-semibold text-stone-800">Buket belum ditemukan</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">Coba gunakan kata kunci lain atau jelajahi seluruh koleksi kami.</p>
                <a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Lihat semua buket</a>
            </div>
        @endforelse

        @if($products->hasPages())
            <div class="mt-12 border-t border-stone-200 pt-8">{{ $products->links() }}</div>
        @endif
    </section>

@endsection
