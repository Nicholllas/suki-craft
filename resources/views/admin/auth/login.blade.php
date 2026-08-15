@extends('layouts.admin-auth')

@section('title', 'Masuk Admin')

@section('content')
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akses tim</p><h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800">Selamat datang kembali.</h1><p class="mt-3 text-sm leading-6 text-stone-500">Masuk untuk mengelola operasional Suki Craft.</p></div>
    @if(session('status'))<div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">@csrf
        <div><label for="email" class="text-sm font-semibold text-stone-700">Email admin</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="mt-2 block h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm focus:border-rose-300 focus:ring-rose-200" placeholder="nama@sukicraft.com">@error('email')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
        <div><div class="flex items-center justify-between"><label for="password" class="text-sm font-semibold text-stone-700">Password</label><a href="{{ route('admin.password.request') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700">Lupa password?</a></div><input id="password" name="password" type="password" required autocomplete="current-password" class="mt-2 block h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm focus:border-rose-300 focus:ring-rose-200" placeholder="Masukkan password"></div>
        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-stone-500"><input type="checkbox" name="remember" class="h-4 w-4 rounded border-stone-300 text-rose-500 focus:ring-rose-200">Ingat saya di perangkat ini</label>
        <button type="submit" class="flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600">Masuk ke dashboard</button>
    </form>
    <p class="mt-8 text-center text-xs leading-5 text-stone-400">Halaman ini khusus untuk tim Suki Craft. Untuk berbelanja, kembali ke <a href="{{ route('home') }}" class="font-semibold text-rose-500">halaman toko</a>.</p>
@endsection
