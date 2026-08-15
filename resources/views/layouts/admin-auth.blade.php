<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') · Suki Craft</title>
    <meta name="robots" content="noindex, nofollow">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbfaf7] font-sans text-stone-800 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        <section class="relative hidden overflow-hidden bg-[#f9e7e8] p-12 lg:flex lg:flex-col lg:justify-between"><div class="absolute -left-24 top-16 h-72 w-72 rounded-full bg-rose-200/70 blur-3xl"></div><div class="absolute -bottom-28 right-0 h-96 w-96 rounded-full bg-amber-100/90 blur-3xl"></div><a href="{{ route('home') }}" class="relative z-10 flex items-center gap-3"><span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/80 text-rose-600 shadow-sm"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a4.25 4.25 0 0 0-4.13 3.25A4.25 4.25 0 0 0 4.25 12c0 2.35 1.9 4.25 4.25 4.25.33 0 .65-.04.95-.11A4.25 4.25 0 0 0 12 21.75a4.25 4.25 0 0 0 2.55-5.61c.3.07.62.11.95.11a4.25 4.25 0 0 0 .38-8.48A4.25 4.25 0 0 0 12 2.25Z" /></svg></span><span><span class="block font-serif text-2xl font-semibold">Suki Craft</span><span class="text-[10px] font-bold uppercase tracking-[0.2em] text-rose-500">Admin studio</span></span></a><div class="relative z-10 max-w-lg"><p class="text-sm font-bold uppercase tracking-[0.2em] text-rose-500">Ruang kerja yang tenang</p><h1 class="mt-4 font-serif text-5xl font-semibold leading-tight tracking-tight text-stone-800">Merangkai setiap momen dengan penuh perhatian.</h1><p class="mt-5 max-w-md text-base leading-7 text-stone-600">Kelola pesanan, stok, dan cerita pelanggan Suki Craft dari satu ruang kerja yang hangat.</p><div class="mt-10 grid max-w-sm grid-cols-2 gap-3"><div class="rounded-2xl border border-white/70 bg-white/60 p-4"><p class="font-serif text-2xl font-semibold text-stone-800">✿</p><p class="mt-2 text-xs leading-5 text-stone-600">Setiap detail dirancang untuk momen spesial.</p></div><div class="rounded-2xl border border-white/70 bg-white/60 p-4"><p class="font-serif text-2xl font-semibold text-stone-800">♡</p><p class="mt-2 text-xs leading-5 text-stone-600">Ruang aman khusus untuk tim internal.</p></div></div></div><p class="relative z-10 text-xs text-stone-500">&copy; {{ date('Y') }} Suki Craft</p></section>
        <section class="flex items-center justify-center px-5 py-10 sm:px-8"><div class="w-full max-w-md"><a href="{{ route('home') }}" class="mb-10 flex items-center gap-2 lg:hidden"><span class="grid h-10 w-10 place-items-center rounded-2xl bg-rose-100 text-rose-600">✿</span><span class="font-serif text-xl font-semibold">Suki Craft</span></a>@yield('content')</div></section>
    </main>
</body>
</html>
