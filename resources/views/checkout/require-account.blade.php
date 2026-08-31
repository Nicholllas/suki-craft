@extends('layouts.store')

@section('title', __('store.checkout.require_account.title'))

@section('content')
    <section class="mx-auto max-w-lg px-4 py-10 sm:px-6 sm:py-14">
        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">{{ __('store.checkout.require_account.eyebrow') }}</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">{{ __('store.checkout.require_account.heading') }}</h1>
            <p class="mt-3 text-sm leading-6 text-stone-500">{{ __('store.checkout.require_account.description') }}</p>

            <div class="mt-7 rounded-2xl bg-rose-50 p-4">
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-rose-500">{{ __('store.checkout.require_account.cart_summary') }}</p>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4 text-stone-600">
                        <dt>{{ __('store.checkout.require_account.bouquet_count') }}</dt>
                        <dd class="font-semibold text-stone-800">{{ __('store.checkout.require_account.bouquet_count_value', ['count' => $itemCount]) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-t border-rose-100 pt-3 text-stone-600">
                        <dt>{{ __('store.checkout.require_account.temporary_total') }}</dt>
                        <dd class="font-serif text-xl font-semibold text-stone-800">Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-7 grid gap-3">
                <a href="{{ route('customer.register') }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-rose-300 focus:ring-offset-2">{{ __('store.checkout.require_account.create_account') }}</a>
                <a href="{{ route('customer.login') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-stone-200 px-5 text-sm font-semibold text-stone-700 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:ring-offset-2">{{ __('store.checkout.require_account.sign_in') }}</a>
            </div>
        </div>

        <a href="{{ route('cart.index') }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-stone-500 transition hover:text-rose-600">
            <span aria-hidden="true">←</span>
            {{ __('store.checkout.require_account.back_to_cart') }}
        </a>
    </section>
@endsection
