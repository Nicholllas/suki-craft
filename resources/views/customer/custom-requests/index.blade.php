@extends('layouts.store')

@section('title', 'Permintaan Custom Bouquet | Suki Craft')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akun Suki Craft</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Permintaan Custom Bouquet</h1>
            <p class="mt-2 text-sm text-stone-500">Pantau penawaran dan lanjutkan ke keranjang setelah harga disetujui.</p>
        </div>

        <div class="mt-6"><x-customer.account-navigation active="custom-requests" /></div>

        <div class="mt-6 space-y-4">
            @forelse ($customRequests as $customRequest)
                <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-mono text-sm font-bold tracking-wide text-rose-600">{{ $customRequest->request_number }}</p>
                            <h2 class="mt-2 font-serif text-xl font-semibold text-stone-800">{{ $customRequest->product->name }}</h2>
                            <p class="mt-2 text-sm text-stone-500">Dibutuhkan {{ $customRequest->needed_date->locale('id')->translatedFormat('d F Y') }} · dibuat {{ $customRequest->created_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p>
                            @if ($customRequest->quoted_price !== null)
                                <p class="mt-3 text-sm font-semibold text-stone-800">Penawaran: Rp{{ number_format($customRequest->quoted_price, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <span class="inline-flex w-fit rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">{{ $customRequest->status->label() }}</span>
                    </div>
                    <div class="mt-5"><a href="{{ route('customer.custom-requests.show', $customRequest) }}" class="inline-flex items-center justify-center rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Lihat detail permintaan</a></div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-rose-200 bg-rose-50/60 px-6 py-14 text-center">
                    <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-2xl text-rose-400">✿</div>
                    <h2 class="mt-5 font-serif text-2xl font-semibold text-stone-800">Belum ada permintaan custom</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-stone-500">Mulai dari halaman produk Buket Custom untuk menceritakan ide rangkaianmu.</p>
                    <a href="{{ route('products.index', ['category' => 'buket-custom']) }}" class="mt-6 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Buat buket custom</a>
                </div>
            @endforelse
        </div>

        @if ($customRequests->hasPages())
            <div class="mt-7">{{ $customRequests->links() }}</div>
        @endif
    </section>
@endsection