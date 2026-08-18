@extends('layouts.store')

@section('title', 'Keranjang Belanja | Suki Craft')

@section('content')
    @php($items = $cart?->itemGroups ?? collect())

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="flex items-end justify-between gap-4 border-b border-stone-200 pb-6">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Pesananmu</p><h1 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-stone-800">Keranjang belanja</h1></div>
            <a href="{{ route('products.index') }}" class="hidden text-sm font-semibold text-rose-600 hover:text-rose-700 sm:inline">Tambah buket lain</a>
        </div>

        @if(session('success'))
            <div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="mt-6 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ session('error') }}</div>
        @endif

        @if($items->isEmpty())
            <div class="mt-8 rounded-3xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-16 text-center"><div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-2xl text-rose-400 shadow-sm">✿</div><h2 class="mt-5 font-serif text-2xl font-semibold text-stone-800">Keranjangmu masih kosong</h2><p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">Jelajahi koleksi bunga segar kami dan temukan buket untuk momen spesialmu.</p><a href="{{ route('products.index') }}" class="mt-6 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Jelajahi koleksi buket</a></div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start">
                <div class="space-y-4">
                    @foreach($items as $item)
                        @php($imagePath = $item->product->primary_image?->path ?? $item->product->image)
                        <article class="rounded-2xl border border-stone-200 bg-white p-4 sm:p-5">
                            <div class="flex gap-4 sm:gap-5">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="h-24 w-20 shrink-0 overflow-hidden rounded-xl bg-rose-50 sm:h-28 sm:w-24">
                                    @if($imagePath)
                                        <img src="{{ Storage::url($imagePath) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="grid h-full place-items-center text-2xl text-rose-300">✿</div>
                                    @endif
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex gap-3">
                                        <div class="min-w-0 flex-1"><p class="text-xs font-semibold uppercase tracking-[0.13em] text-rose-500">{{ $item->product->category->name }}</p><h2 class="mt-1 truncate font-serif text-xl font-semibold text-stone-800">{{ $item->product->name }}</h2>@if($item->variants->isNotEmpty())<ul class="mt-1 space-y-1 text-sm text-stone-500">@foreach($item->variants as $variant)<li>{{ $variant->productVariant->label }}@if($variant->quantity_in_bundle > 1) · {{ $variant->quantity_in_bundle }}×@endif</li>@endforeach</ul>@endif</div>
                                        <form method="POST" action="{{ route('cart.remove', $item->id) }}" data-confirm="Buket ini akan dihapus dari keranjang." data-confirm-button="Ya, hapus" data-confirm-title="Hapus buket dari keranjang?">@csrf @method('DELETE')<button class="grid h-9 w-9 place-items-center rounded-full text-stone-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Hapus {{ $item->product->name }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m6 7 .75 12h10.5L18 7M9.5 10.5v5m5-5v5M9 7V4.5h6V7M4.5 7h15" /></svg></button></form>
                                    </div>
                                    @if($item->card_message || $item->special_note)
                                        <div class="mt-3 space-y-1 rounded-xl bg-stone-50 px-3 py-2 text-xs leading-5 text-stone-500">@if($item->card_message)<p><span class="font-semibold text-stone-600">Kartu:</span> {{ $item->card_message }}</p>@endif @if($item->special_note)<p><span class="font-semibold text-stone-600">Catatan:</span> {{ $item->special_note }}</p>@endif</div>
                                    @endif
                                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                                        <div><p class="text-xs text-stone-400">Harga per buket</p><p class="mt-1 text-sm font-semibold text-stone-700">Rp{{ number_format($item->bundle_subtotal, 0, ',', '.') }}</p></div>
                                        <div class="flex items-center gap-4">
                                            <div class="flex items-center rounded-xl border border-stone-200"><form method="POST" action="{{ route('cart.update', $item->id) }}">@csrf @method('PATCH')<input type="hidden" name="quantity" value="{{ max(1, $item->bundle_quantity - 1) }}"><button class="grid h-9 w-9 place-items-center text-lg text-stone-500 transition hover:text-rose-600 disabled:opacity-30" @disabled($item->bundle_quantity === 1) aria-label="Kurangi jumlah">−</button></form><span class="w-8 text-center text-sm font-semibold text-stone-800">{{ $item->bundle_quantity }}</span><form method="POST" action="{{ route('cart.update', $item->id) }}">@csrf @method('PATCH')<input type="hidden" name="quantity" value="{{ min(99, $item->bundle_quantity + 1) }}"><button class="grid h-9 w-9 place-items-center text-lg text-stone-500 transition hover:text-rose-600" aria-label="Tambah jumlah">+</button></form></div>
                                            <div class="text-right"><p class="text-xs text-stone-400">Subtotal</p><p class="mt-1 text-base font-semibold text-stone-800">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 hover:text-rose-700"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m19 12H5m5-5-5 5 5 5" /></svg>Tambah buket lain</a>
                </div>

                <aside class="sticky bottom-3 rounded-2xl border border-stone-200 bg-white p-5 shadow-xl shadow-stone-900/5 lg:bottom-auto lg:top-24">
                    <h2 class="font-serif text-2xl font-semibold text-stone-800">Ringkasan pesanan</h2>
                    <div class="mt-5 flex items-center justify-between border-b border-stone-100 pb-4 text-sm text-stone-600"><span>Subtotal ({{ $items->sum('bundle_quantity') }} buket)</span><span class="font-semibold text-stone-800">Rp{{ number_format($total, 0, ',', '.') }}</span></div>
                    <p class="mt-4 text-xs leading-5 text-stone-500">Biaya pengiriman dan pilihan waktu kirim akan dihitung pada tahap checkout.</p>
                    <div class="mt-5 flex items-end justify-between"><span class="text-sm font-semibold text-stone-800">Total sementara</span><span class="font-serif text-2xl font-semibold text-stone-800">Rp{{ number_format($total, 0, ',', '.') }}</span></div>
                    <a href="{{ route('checkout.index') }}" class="mt-6 inline-flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">Lanjut ke checkout</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
