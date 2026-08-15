@extends('layouts.admin')

@section('title', 'Produk Buket')
@section('page-title', 'Produk Buket')

@section('content')
    <div><div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Katalog</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Produk buket</h1><p class="mt-2 text-sm text-stone-500">Atur koleksi, harga dasar, varian, dan galeri foto buket.</p></div><a href="{{ route('admin.products.create') }}" class="inline-flex h-11 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">+ Tambah produk</a></div>
        <x-card class="mt-7" padding="p-4"><form class="grid gap-3 md:grid-cols-[minmax(0,1fr)_12rem_10rem_auto]"><input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Cari nama produk..." class="h-11 rounded-xl border-stone-200 bg-white px-4 text-sm focus:border-rose-300 focus:ring-rose-200"><select name="category" class="h-11 rounded-xl border-stone-200 bg-white px-3 text-sm focus:border-rose-300 focus:ring-rose-200"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select><select name="status" class="h-11 rounded-xl border-stone-200 bg-white px-3 text-sm focus:border-rose-300 focus:ring-rose-200"><option value="">Semua status</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option></select><button class="h-11 rounded-xl bg-stone-800 px-5 text-sm font-semibold text-white">Filter</button></form></x-card>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse($products as $product)
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="relative aspect-[16/10] bg-rose-50">
                        @if($product->primary_image)
                            <img src="{{ Storage::url($product->primary_image->path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="grid h-full place-items-center text-3xl text-rose-300">✿</div>
                        @endif

                        @if($product->is_featured)
                            <span class="absolute left-3 top-3 rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700">Unggulan</span>
                        @endif
                    </div>

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-rose-500">{{ $product->category->name }}</p>
                                <h2 class="mt-1 truncate font-serif text-xl font-semibold text-stone-800">{{ $product->name }}</h2>
                            </div>

                            <x-status-badge :status="$product->is_active ? 'Aktif' : 'Nonaktif'" />
                        </div>

                        <p class="mt-4 text-lg font-semibold text-stone-800">Rp{{ number_format($product->final_price, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-stone-400">{{ $product->variants_count }} varian · {{ $product->images->count() }} foto</p>

                        <div class="mt-5 flex items-center justify-between border-t border-stone-100 pt-4">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">Kelola produk</a>

                            <form method="POST" action="{{ route('admin.products.toggle', $product) }}" onsubmit="return confirm('Ubah status produk ini?')">
                                @csrf
                                @method('PATCH')
                                <button class="text-xs font-semibold text-stone-500 hover:text-stone-800">{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @empty
                <div class="sm:col-span-2 xl:col-span-3">
                    <x-card>
                        <x-empty-state title="Belum ada produk buket" description="Tambahkan produk pertama beserta varian dan foto galeri untuk mulai membangun katalog." />
                    </x-card>
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $products->links() }}</div>
    </div>
@endsection
