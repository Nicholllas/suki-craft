@props(['active'])

<nav class="grid gap-1 rounded-2xl border border-stone-200 bg-white p-2 text-sm font-semibold text-stone-600 sm:flex sm:items-center" aria-label="{{ __('storefront.account_pages.navigation') }}">
    <a href="{{ route('customer.profile.edit') }}" @class(['rounded-xl px-4 py-3 transition hover:bg-rose-50 hover:text-rose-600', 'bg-rose-50 text-rose-600' => $active === 'profile'])>{{ __('store.account.profile') }}</a>
    <a href="{{ route('customer.orders.index') }}" @class(['rounded-xl px-4 py-3 transition hover:bg-rose-50 hover:text-rose-600', 'bg-rose-50 text-rose-600' => $active === 'orders'])>{{ __('store.account.orders') }}</a>
</nav>
