@props(['active'])

<nav class="grid gap-1 rounded-2xl border border-stone-200 bg-white p-2 text-sm font-semibold text-stone-600 sm:flex sm:items-center" aria-label="Navigasi akun">
    <a href="{{ route('customer.profile.edit') }}" @class(['rounded-xl px-4 py-3 transition hover:bg-rose-50 hover:text-rose-600', 'bg-rose-50 text-rose-600' => $active === 'profile'])>Profil</a>
    <a href="{{ route('customer.orders.index') }}" @class(['rounded-xl px-4 py-3 transition hover:bg-rose-50 hover:text-rose-600', 'bg-rose-50 text-rose-600' => $active === 'orders'])>Riwayat Pesanan</a>
    <form method="POST" action="{{ route('customer.logout') }}" class="sm:ml-auto">@csrf<button type="submit" class="w-full rounded-xl px-4 py-3 text-left text-rose-600 transition hover:bg-rose-50 sm:w-auto">Keluar</button></form>
</nav>
