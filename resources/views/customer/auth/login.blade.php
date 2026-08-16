@extends('layouts.store')

@section('title', 'Masuk | Suki Craft')

@section('content')
    <section class="mx-auto max-w-lg px-4 py-10 sm:px-6 sm:py-14">
        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akun Suki Craft</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Selamat datang kembali</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">Masuk untuk melihat profil dan riwayat pesanan Anda.</p>
            @if(session('status'))<div class="mt-6 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>@endif
            <form method="POST" action="{{ route('customer.login.store') }}" class="mt-7 space-y-5">@csrf
                <div><label for="login" class="text-sm font-semibold text-stone-700">Email atau nomor WhatsApp</label><input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200 @error('login') border-rose-400 @enderror" placeholder="nama@email.com atau 08xxxxxxxxxx">@error('login')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><div class="flex items-center justify-between gap-3"><label for="password" class="text-sm font-semibold text-stone-700">Password</label><a href="{{ route('customer.password.request') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Lupa password?</a></div><input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200"></div>
                <label class="flex items-center gap-2.5 text-sm text-stone-500"><input type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 text-rose-500 focus:ring-rose-200">Ingat saya di perangkat ini</label>
                <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600">Masuk</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-500">Belum punya akun? <a href="{{ route('customer.register') }}" class="font-semibold text-rose-600 hover:text-rose-700">Daftar</a></p>
        </div>
    </section>
@endsection
