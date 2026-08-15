@extends('layouts.store')

@section('title', 'Lacak Pesanan | Suki Craft')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 sm:py-16">
        <div class="rounded-3xl border border-rose-100 bg-rose-50 px-6 py-8 text-center sm:px-10">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-rose-500 shadow-sm"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-4.5-4.5 4.5 4.5-4.5 4.5M3.75 6.75h10.5m-10.5 10.5h10.5" /></svg></div>
            <p class="mt-5 text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Suki Craft</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-stone-800">Lacak pesananmu</h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-stone-600">Masukkan nomor pesanan dan nomor WhatsApp yang digunakan saat checkout untuk melihat pembaruan terbaru.</p>
        </div>

        <form method="POST" action="{{ route('tracking.store') }}" class="mt-6 rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-7">
            @csrf
            <label for="order_number" class="block text-sm font-semibold text-stone-800">Nomor pesanan</label>
            <input id="order_number" name="order_number" value="{{ old('order_number') }}" required autocomplete="off" placeholder="Contoh: SC-20260816-0001" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 font-mono text-sm uppercase text-stone-800 placeholder:font-sans placeholder:normal-case placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200">
            @error('order_number')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <label for="phone" class="mt-5 block text-sm font-semibold text-stone-800">Nomor WhatsApp saat checkout</label>
            <input id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" inputmode="tel" placeholder="Contoh: 081234567890" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200">
            @error('phone')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="mt-6 inline-flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">Lacak pesanan</button>
        </form>
    </section>
@endsection
