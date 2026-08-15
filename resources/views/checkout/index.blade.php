@extends('layouts.store')

@section('title', 'Checkout | Suki Craft')

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-20 text-center sm:px-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Segera hadir</p>
        <h1 class="mt-4 font-serif text-4xl font-semibold text-stone-800">Checkout sedang kami siapkan.</h1>
        <p class="mx-auto mt-4 max-w-lg text-sm leading-6 text-stone-600">Sementara ini, Anda masih dapat meninjau dan mengatur pilihan buket di keranjang belanja.</p>
        <a href="{{ route('cart.index') }}" class="mt-7 inline-flex rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-600">Kembali ke keranjang</a>
    </section>
@endsection
