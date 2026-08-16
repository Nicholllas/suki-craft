@extends('layouts.store')

@section('title', 'Daftar | Suki Craft')

@section('content')
    <section class="mx-auto max-w-lg px-4 py-10 sm:px-6 sm:py-14">
        <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm sm:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Akun Suki Craft</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Buat akun baru</h1>
            <p class="mt-2 text-sm leading-6 text-stone-500">Simpan pesanan dan kelola riwayat belanja Anda dengan lebih mudah.</p>
            <form method="POST" action="{{ route('customer.register.store') }}" class="mt-7 space-y-5">@csrf
                <div><label for="name" class="text-sm font-semibold text-stone-700">Nama lengkap</label><input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200 @error('name') border-rose-400 @enderror">@error('name')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="email" class="text-sm font-semibold text-stone-700">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200 @error('email') border-rose-400 @enderror">@error('email')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="phone" class="text-sm font-semibold text-stone-700">Nomor WhatsApp</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200 @error('phone') border-rose-400 @enderror" placeholder="08xxxxxxxxxx"><p class="mt-2 text-xs text-stone-400">Gunakan format 08xx atau +628xx.</p>@error('phone')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="password" class="text-sm font-semibold text-stone-700">Password</label><input id="password" name="password" type="password" required autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200 @error('password') border-rose-400 @enderror">@error('password')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror</div>
                <div><label for="password_confirmation" class="text-sm font-semibold text-stone-700">Konfirmasi password</label><input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="mt-2 h-12 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200"></div>
                <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:bg-rose-600">Daftar</button>
            </form>
            <p class="mt-6 text-center text-sm text-stone-500">Sudah punya akun? <a href="{{ route('customer.login') }}" class="font-semibold text-rose-600 hover:text-rose-700">Masuk</a></p>
        </div>
    </section>
@endsection
