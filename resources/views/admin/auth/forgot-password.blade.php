@extends('layouts.admin-auth')

@section('title', 'Lupa Password Admin')

@section('content')
    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 hover:text-rose-700">← Kembali ke masuk</a><div class="mt-8"><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pemulihan akses</p><h1 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800">Lupa password?</h1><p class="mt-3 text-sm leading-6 text-stone-500">Masukkan email admin Anda. Kami akan mengirimkan tautan untuk membuat password baru.</p></div>
    @if(session('status'))<div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('admin.password.email') }}" class="mt-8 space-y-5">@csrf<div><label for="email" class="text-sm font-semibold text-stone-700">Email admin</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 block h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm focus:border-rose-300 focus:ring-rose-200" placeholder="nama@sukicraft.com">@error('email')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div><button type="submit" class="flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600">Kirim tautan reset</button></form>
@endsection
