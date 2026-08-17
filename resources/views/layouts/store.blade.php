<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Suki Craft')</title>
    <meta name="description" content="@yield('description', 'Suki Craft - Flower & Gift Shop')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Suki Craft')">
    <meta property="og:description" content="@yield('description', 'Suki Craft - Flower & Gift Shop')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Suki Craft">
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-[#fffdf9] font-sans text-stone-800 antialiased">
    <div class="border-b border-rose-100 bg-rose-50 px-4 py-2 text-center text-xs font-medium tracking-wide text-rose-700 sm:text-sm">Gratis kartu ucapan untuk setiap pesanan buket</div>

    @php($cartItemCount = app(\App\Services\CartService::class)->getItemCount())

    <header x-data="{ accountOpen: false, open: false, cartCount: {{ $cartItemCount }} }" @cart-updated.window="cartCount = $event.detail.count ?? 0" class="sticky top-0 z-50 border-b border-stone-100 bg-[#fffdf9]/95 backdrop-blur">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="group flex items-center gap-2.5" aria-label="Suki Craft beranda">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-rose-100 text-rose-600 transition group-hover:scale-105">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.25a4.25 4.25 0 0 0-4.13 3.25A4.25 4.25 0 0 0 4.25 12c0 2.35 1.9 4.25 4.25 4.25.33 0 .65-.04.95-.11A4.25 4.25 0 0 0 12 21.75a4.25 4.25 0 0 0 2.55-5.61c.3.07.62.11.95.11a4.25 4.25 0 0 0 .38-8.48A4.25 4.25 0 0 0 12 2.25Zm0 4.25a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm-3.68 5.47a1.5 1.5 0 1 1 2.6 1.5 1.5 1.5 0 0 1-2.6-1.5Zm6.86 1.5a1.5 1.5 0 1 1 .5-2.05 1.5 1.5 0 0 1-.5 2.05Z" /></svg>
                </span>
                <span class="leading-none"><span class="block font-serif text-xl font-semibold tracking-tight text-stone-800">Suki Craft</span><span class="mt-1 block text-[9px] font-bold uppercase tracking-[0.23em] text-rose-500">Flower & Gift</span></span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium text-stone-600 md:flex" aria-label="Navigasi utama">
                <a href="{{ route('products.index') }}" class="transition hover:text-rose-600">Koleksi</a>
                <a href="{{ route('tracking.create') }}" class="transition hover:text-rose-600">Lacak Pesanan</a>
                <a href="{{ route('about') }}" class="transition hover:text-rose-600">Tentang Kami</a>
                <a href="#cara-pesan" class="transition hover:text-rose-600">Cara Pesan</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @auth('customer')
                    <div class="relative"><button @click="accountOpen = !accountOpen" @click.outside="accountOpen = false" :aria-expanded="accountOpen" class="inline-flex items-center gap-2 rounded-full px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-rose-50 hover:text-rose-600"><span class="max-w-28 truncate">{{ auth('customer')->user()->name }}</span><svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 0 1 1.09 1.03l-4.25 4.5a.75.75 0 0 1-1.09 0l-4.25-4.5a.75.75 0 0 1 .02-1.05Z" clip-rule="evenodd" /></svg></button><div x-cloak x-show="accountOpen" x-transition.origin.top.right class="absolute right-0 top-full mt-2 w-52 rounded-2xl border border-stone-200 bg-white p-2 shadow-xl shadow-stone-900/10"><a href="{{ route('customer.profile.edit') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-stone-600 hover:bg-rose-50 hover:text-rose-600">Profil</a><a href="{{ route('customer.orders.index') }}" class="block rounded-xl px-3 py-2.5 text-sm font-semibold text-stone-600 hover:bg-rose-50 hover:text-rose-600">Riwayat Pesanan</a><form method="POST" action="{{ route('customer.logout') }}">@csrf<button type="submit" class="mt-1 w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50">Keluar</button></form></div></div>
                @else
                    <a href="{{ route('customer.login') }}" class="text-sm font-medium text-stone-600 transition hover:text-rose-600">Masuk</a>
                    <a href="{{ route('customer.register') }}" class="text-sm font-semibold text-rose-600 transition hover:text-rose-700">Daftar</a>
                @endauth
                <a href="{{ route('cart.index') }}" class="relative grid h-11 w-11 place-items-center rounded-full text-stone-700 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Buka keranjang">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h1.15c.67 0 1.25.47 1.39 1.13l.22 1.04m0 0 1.2 5.67a1.5 1.5 0 0 0 1.47 1.19h7.59a1.5 1.5 0 0 0 1.44-1.08l1.06-3.68a.75.75 0 0 0-.72-.96H6.51Zm3 13.08a1.13 1.13 0 1 0 2.25 0 1.13 1.13 0 0 0-2.25 0Zm9 0a1.13 1.13 0 1 0 2.25 0 1.13 1.13 0 0 0-2.25 0Z" /></svg>
                    <span x-cloak x-show="cartCount > 0" x-text="cartCount > 99 ? '99+' : cartCount" class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-5 text-white"></span>
                </a>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-rose-600 hover:shadow-rose-200">Lihat Buket <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.22 3.22a.75.75 0 0 1 1.06 0l6.25 6.25a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 1 1-1.06-1.06l4.97-4.97H3.06a.75.75 0 0 1 0-1.5h12.13l-4.97-4.97a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></a>
            </div>

            <div class="flex items-center gap-1 md:hidden">
                <a href="{{ route('cart.index') }}" class="relative grid h-10 w-10 place-items-center rounded-full text-stone-700 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Buka keranjang">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.5h1.15c.67 0 1.25.47 1.39 1.13l.22 1.04m0 0 1.2 5.67a1.5 1.5 0 0 0 1.47 1.19h7.59a1.5 1.5 0 0 0 1.44-1.08l1.06-3.68a.75.75 0 0 0-.72-.96H6.51Zm3 13.08a1.13 1.13 0 1 0 2.25 0 1.13 1.13 0 0 0-2.25 0Zm9 0a1.13 1.13 0 1 0 2.25 0 1.13 1.13 0 0 0-2.25 0Z" /></svg>
                    <span x-cloak x-show="cartCount > 0" x-text="cartCount > 99 ? '99+' : cartCount" class="absolute -right-1 -top-1 grid min-w-5 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold leading-5 text-white"></span>
                </a>
                <button @click="open = !open" :aria-expanded="open" class="grid h-10 w-10 place-items-center rounded-full text-stone-700 transition hover:bg-rose-50" aria-label="Buka menu">
                    <svg x-show="!open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg x-cloak x-show="open" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>
        </div>
        <div x-cloak x-show="open" x-transition.origin.top class="border-t border-stone-100 bg-[#fffdf9] px-4 pb-5 pt-2 shadow-lg md:hidden">
            <nav class="mx-auto grid max-w-7xl gap-1 text-sm font-medium text-stone-700" aria-label="Navigasi mobile">
                <a @click="open = false" href="{{ route('products.index') }}" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Koleksi</a>
                <a @click="open = false" href="{{ route('tracking.create') }}" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Lacak Pesanan</a>
                <a @click="open = false" href="{{ route('about') }}" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Tentang Kami</a>
                <a @click="open = false" href="#cara-pesan" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Cara Pesan</a>
                @auth('customer')
                    <a href="{{ route('customer.profile.edit') }}" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Profil</a>
                    <a href="{{ route('customer.orders.index') }}" class="rounded-xl px-4 py-3 hover:bg-rose-50 hover:text-rose-600">Riwayat Pesanan</a>
                    <form method="POST" action="{{ route('customer.logout') }}">@csrf<button type="submit" class="w-full rounded-xl px-4 py-3 text-left text-rose-600 hover:bg-rose-50">Keluar</button></form>
                @else
                    <div class="grid grid-cols-2 gap-2 px-1 py-2"><a href="{{ route('customer.login') }}" class="rounded-xl border border-rose-200 px-4 py-3 text-center font-semibold text-rose-600 hover:bg-rose-50">Masuk</a><a href="{{ route('customer.register') }}" class="rounded-xl bg-rose-500 px-4 py-3 text-center font-semibold text-white hover:bg-rose-600">Daftar</a></div>
                @endauth
                <a href="{{ route('products.index') }}" class="mt-2 rounded-xl bg-rose-500 px-4 py-3 text-center font-semibold text-white">Lihat Semua Buket</a>
                <a href="{{ route('cart.index') }}" class="rounded-xl px-4 py-3 text-center font-semibold text-stone-600 hover:bg-rose-50 hover:text-rose-600">Keranjang <span x-show="cartCount > 0" x-text="`(${cartCount})`"></span></a>
            </nav>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="bg-stone-900 text-stone-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div class="lg:col-span-2"><a href="{{ route('home') }}" class="font-serif text-2xl font-semibold text-white">Suki Craft</a><p class="mt-4 max-w-sm text-sm leading-6 text-stone-400">Buket bunga yang dirangkai dengan penuh rasa, untuk membantu Anda menyampaikan hal yang tak selalu mudah diucapkan.</p></div>
            <div><h2 class="text-sm font-semibold text-white">Jelajahi</h2><ul class="mt-4 space-y-3 text-sm"><li><a href="{{ route('products.index') }}" class="transition hover:text-rose-300">Koleksi Buket</a></li><li><a href="{{ route('tracking.create') }}" class="transition hover:text-rose-300">Lacak Pesanan</a></li><li><a href="{{ route('about') }}" class="transition hover:text-rose-300">Tentang Suki Craft</a></li><li><a href="#cara-pesan" class="transition hover:text-rose-300">Cara Pemesanan</a></li></ul></div>
            <div><h2 class="text-sm font-semibold text-white">Hubungi Kami</h2><ul class="mt-4 space-y-3 text-sm text-stone-400"><li class="flex items-center gap-2"><svg class="h-4 w-4 text-rose-300" viewBox="0 0 24 24" fill="currentColor"><path d="M2.25 6.75A2.25 2.25 0 0 1 4.5 4.5h15a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75Zm2.09-.75L12 11.74 19.66 6H4.34Z" /></svg>hello@sukicraft.com</li><li class="flex items-center gap-2"><svg class="h-4 w-4 text-rose-300" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a7.5 7.5 0 0 0-7.5 7.5c0 5.625 7.5 12 7.5 12s7.5-6.375 7.5-12a7.5 7.5 0 0 0-7.5-7.5Zm0 10.5a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z" /></svg>Indonesia</li></ul></div>
        </div>
        <div class="border-t border-stone-800 px-4 py-5 text-center text-xs text-stone-500">&copy; {{ date('Y') }} Suki Craft. Made with love.</div>
    </footer>
</body>
</html>
