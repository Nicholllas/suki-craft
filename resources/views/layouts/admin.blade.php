<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · Suki Craft</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebarOpen: false, sidebarCompact: false, notificationsOpen: false, profileOpen: false }" class="min-h-screen bg-[#fbfaf7] font-sans text-stone-800 antialiased">
    <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-stone-900/40 backdrop-blur-[2px] lg:hidden" @click="sidebarOpen = false"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-rose-100 bg-[#fffdf9] shadow-2xl shadow-stone-900/10 transition-transform duration-300 lg:translate-x-0" aria-label="Navigasi admin">
        <div class="flex h-20 items-center justify-between border-b border-rose-100 px-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="Suki Craft admin dashboard">
                <span class="grid h-10 w-10 place-items-center rounded-2xl bg-rose-100 text-rose-600"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.25a4.25 4.25 0 0 0-4.13 3.25A4.25 4.25 0 0 0 4.25 12c0 2.35 1.9 4.25 4.25 4.25.33 0 .65-.04.95-.11A4.25 4.25 0 0 0 12 21.75a4.25 4.25 0 0 0 2.55-5.61c.3.07.62.11.95.11a4.25 4.25 0 0 0 .38-8.48A4.25 4.25 0 0 0 12 2.25Zm0 4.25a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-3.68 5.47a1.5 1.5 0 1 1 2.6 1.5 1.5 1.5 0 0 1-2.6-1.5Zm6.86 1.5a1.5 1.5 0 1 1 .5-2.05 1.5 1.5 0 0 1-.5 2.05Z" /></svg></span>
                <span><span class="block font-serif text-xl font-semibold tracking-tight">Suki Craft</span><span class="block text-[9px] font-bold uppercase tracking-[0.2em] text-rose-500">Admin studio</span></span>
            </a>
            <button @click="sidebarOpen = false" class="grid h-10 w-10 place-items-center rounded-xl text-stone-500 hover:bg-rose-50 lg:hidden" aria-label="Tutup navigasi"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg></button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-5">
            <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Utama</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12 12 3.75 20.25 12v8.25H14.5V15h-5v5.25H3.75V12Z" /></svg>Dashboard</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.*')">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9l3 3v13.5H4.5V6.75l3-3ZM8.25 10.5h7.5m-7.5 3h7.5m-7.5 3h4.5" /></svg>
                    Semua Pesanan
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.payment-verifications.index')" :active="request()->routeIs('admin.payment-verifications.*')">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75h9l3 3v13.5H4.5V6.75l3-3ZM8.25 10.5h7.5m-7.5 3h7.5m-7.5 3h4.5" /></svg>
                    Verifikasi pembayaran
                    @if ($pendingPaymentVerificationCount > 0)
                        <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">{{ $pendingPaymentVerificationCount }}</span>
                    @endif
                </x-admin.nav-link>
            </div>

            <p class="mt-7 px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Katalog</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 12a8.25 8.25 0 1 1-16.5 0 8.25 8.25 0 0 1 16.5 0ZM12 7.5v9m-4.5-4.5h9" /></svg>Produk & Buket</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m7.5 3.75 4.5 2.5 4.5-2.5 3 1.75v5L12 14.75 4.5 10.5v-5l3-1.75ZM12 14.75v5.5m7.5-9.75v5L12 19.75l-7.5-4.25v-5" /></svg>Kategori</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.ingredients.index')" :active="request()->routeIs('admin.ingredients.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75 12 3l7.5 3.75M4.5 6.75 12 10.5m-7.5-3.75v10.5L12 21m7.5-14.25v10.5L12 21m0-10.5V21" /></svg>Stok Bahan</x-admin.nav-link>
            </div>

            <p class="mt-7 px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Operasional</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.deliveries.index')" :active="request()->routeIs('admin.deliveries.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h10.5v9.75H3.75V6.75Zm10.5 3h3l3 3v3.75h-6V9.75Zm-7.5 9a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Zm10.5 0a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" /></svg>Pengiriman</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.couriers.index')" :active="request()->routeIs('admin.couriers.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 20.25h7.5M9.75 3.75h4.5m-5.25 4.5h6m-7.5 8.25h9m-9-4.5h9M6.75 3.75h10.5v16.5H6.75V3.75Z" /></svg>Kurir</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.customers.index')" :active="request()->routeIs('admin.customers.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" /></svg>Pelanggan</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.promotions.index')" :active="request()->routeIs('admin.promotions.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 7.5 6.75-3 8.25 3.75-6.75 3L4.5 7.5Zm0 0v8.25l8.25 3.75m0-8.25v8.25m6.75-11.25v8.25l-6.75 3.75" /></svg>Promosi</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.reviews.index')" :active="request()->routeIs('admin.reviews.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 12h7.5m-7.5 3h4.5M5.25 4.5h13.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H12l-3.75 3v-3H5.25a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>Ulasan</x-admin.nav-link>
            </div>

            <p class="mt-7 px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-stone-400">Bisnis</p>
            <div class="space-y-1">
                <x-admin.nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5V9.75m5.25 9.75V4.5M15 19.5v-6.75m5.25 6.75V7.5" /></svg>Laporan & Keuangan</x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75A3.75 3.75 0 1 0 12 8.25a3.75 3.75 0 0 0 0 7.5Z" /><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 12 .96-2.1-2.04-2.04-2.1.96a7.97 7.97 0 0 0-1.37-.8L14.25 5.75h-2.5l-.7 2.27c-.48.2-.94.46-1.37.8l-2.1-.96L5.54 9.9 6.5 12c-.05.48-.05.97 0 1.45l-.96 2.1 2.04 2.04 2.1-.96c.43.34.89.6 1.37.8l.7 2.27h2.5l.7-2.27c.48-.2.94-.46 1.37-.8l2.1.96 2.04-2.04-.96-2.1c.05-.48.05-.97 0-1.45Z" /></svg>Pengaturan</x-admin.nav-link>
                @if(auth('admin')->user()->role->value === 'super_admin')
                    <x-admin.nav-link :href="route('admin.accounts.index')" :active="request()->routeIs('admin.accounts.*')"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" /></svg>Akun Admin</x-admin.nav-link>
                @endif
            </div>
        </nav>

        <div class="m-4 rounded-2xl bg-emerald-50 p-4"><p class="text-xs font-semibold text-emerald-800">Butuh bantuan?</p><p class="mt-1 text-xs leading-5 text-emerald-700">Kelola momen spesial pelanggan dengan lebih tenang.</p><a href="{{ route('home') }}" class="mt-3 inline-flex text-xs font-bold text-emerald-700 hover:text-emerald-900">Lihat toko →</a></div>
    </aside>

    <div class="min-h-screen lg:pl-72">
        <header class="sticky top-0 z-30 border-b border-stone-200/80 bg-[#fbfaf7]/90 backdrop-blur">
            <div class="flex h-20 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button @click="sidebarOpen = true" class="grid h-11 w-11 shrink-0 place-items-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-rose-200 hover:text-rose-600 lg:hidden" aria-label="Buka navigasi"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg></button>
                <label class="relative hidden max-w-md flex-1 lg:block"><span class="sr-only">Cari</span><svg class="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="6" /><path stroke-linecap="round" d="m20 20-4-4" /></svg><input type="search" placeholder="Cari pesanan, produk, pelanggan..." class="h-11 w-full rounded-xl border-stone-200 bg-white pl-10 pr-4 text-sm placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200"></label>
                <div class="ml-auto flex items-center gap-2 sm:gap-3">
                    <p class="hidden text-right text-xs leading-5 text-stone-500 xl:block">{{ now()->locale('id')->translatedFormat('l, d F Y') }}<span class="block font-medium text-stone-700">{{ now()->format('H.i') }} WIB</span></p>
                    <div class="relative" @click.outside="notificationsOpen = false"><button @click="notificationsOpen = !notificationsOpen" :aria-expanded="notificationsOpen" class="relative grid h-11 w-11 place-items-center rounded-xl border border-stone-200 bg-white text-stone-600 transition hover:border-rose-200 hover:text-rose-600" aria-label="Notifikasi"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 18.75h-4.5m9-2.25H5.25l1.5-2.25v-4.5a5.25 5.25 0 0 1 10.5 0v4.5l1.5 2.25Z" /></svg>@if($pendingPaymentVerificationCount > 0)<span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white"></span>@endif</button><div x-cloak x-show="notificationsOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-80 overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-xl shadow-stone-900/10"><div class="flex items-center justify-between border-b border-stone-100 px-4 py-3"><p class="text-sm font-semibold">Notifikasi</p>@if($pendingPaymentVerificationCount > 0)<span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-600">{{ $pendingPaymentVerificationCount }} baru</span>@endif</div><div class="p-3">@if($pendingPaymentVerificationCount > 0)<a href="{{ route('admin.orders.index') }}" class="block rounded-xl bg-amber-50 p-3 text-sm transition hover:bg-amber-100"><p class="font-semibold text-amber-800">{{ $pendingPaymentVerificationCount }} pembayaran menunggu</p><p class="mt-1 text-xs leading-5 text-amber-700">Tinjau bukti pembayaran pelanggan yang baru masuk.</p></a>@else<div class="rounded-xl bg-stone-50 p-3 text-sm"><p class="font-semibold text-stone-700">Tidak ada verifikasi baru</p><p class="mt-1 text-xs leading-5 text-stone-500">Bukti pembayaran yang masuk akan muncul di sini.</p></div>@endif</div></div></div>
                    <div class="relative" @click.outside="profileOpen = false"><button @click="profileOpen = !profileOpen" :aria-expanded="profileOpen" class="flex h-11 items-center gap-2 rounded-xl border border-stone-200 bg-white px-2 pr-3 transition hover:border-rose-200"><span class="grid h-7 w-7 place-items-center rounded-lg bg-rose-100 text-xs font-bold text-rose-600">{{ str(auth('admin')->user()->name)->substr(0, 1)->upper() }}</span><span class="hidden text-left sm:block"><span class="block max-w-24 truncate text-xs font-semibold">{{ auth('admin')->user()->name }}</span><span class="block text-[10px] text-stone-400">{{ auth('admin')->user()->role->label() }}</span></span><svg class="h-3.5 w-3.5 text-stone-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></button><div x-cloak x-show="profileOpen" x-transition.origin.top.right class="absolute right-0 mt-3 w-52 overflow-hidden rounded-2xl border border-stone-100 bg-white p-2 shadow-xl shadow-stone-900/10"><a href="{{ route('admin.profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm text-stone-600 hover:bg-rose-50 hover:text-rose-600">Profil saya</a><form method="POST" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm text-rose-600 hover:bg-rose-50">Keluar</button></form></div></div>
                </div>
            </div>
        </header>

        <main class="px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:py-8">
            <nav class="mb-5 flex items-center gap-2 text-xs text-stone-400" aria-label="Breadcrumb"><a href="{{ route('admin.dashboard') }}" class="hover:text-rose-600">Admin</a><span>/</span><span class="font-medium text-stone-600">@yield('page-title', 'Dashboard')</span></nav>
            @if(session('success'))<div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="mb-5 flex items-center gap-3 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-40 flex border-t border-rose-100 bg-[#fffdf9]/95 px-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] pt-2 backdrop-blur lg:hidden" aria-label="Navigasi cepat">
        <a href="{{ route('admin.dashboard') }}" class="flex flex-1 flex-col items-center gap-1 rounded-xl py-2 text-[10px] font-semibold {{ request()->routeIs('admin.dashboard') ? 'text-rose-600' : 'text-stone-400' }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12 12 3.75 20.25 12v8.25H14.5V15h-5v5.25H3.75V12Z" /></svg>Beranda</a>
        <a href="{{ route('admin.orders.index') }}" class="flex flex-1 flex-col items-center gap-1 rounded-xl py-2 text-[10px] font-semibold {{ request()->routeIs('admin.orders.*') ? 'text-rose-600' : 'text-stone-400' }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M7.5 7.5h9m-9 4.5h9m-9 4.5h5.25M5.25 3.75h13.5v16.5H5.25z" /></svg>Pesanan</a>
        <a href="{{ route('admin.products.index') }}" class="flex flex-1 flex-col items-center gap-1 rounded-xl py-2 text-[10px] font-semibold {{ request()->routeIs('admin.products.*') ? 'text-rose-600' : 'text-stone-400' }}"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-6-6h12" /><circle cx="12" cy="12" r="8.25" /></svg>Produk</a>
        <button @click="sidebarOpen = true" class="flex flex-1 flex-col items-center gap-1 rounded-xl py-2 text-[10px] font-semibold text-stone-400"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="5" cy="12" r="1" fill="currentColor" /><circle cx="12" cy="12" r="1" fill="currentColor" /><circle cx="19" cy="12" r="1" fill="currentColor" /></svg>Lainnya</button>
    </nav>
</body>
</html>
