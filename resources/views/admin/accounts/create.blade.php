@extends('layouts.admin')

@section('title', 'Tambah Akun Admin')
@section('page-title', 'Akun Admin')

@section('content')
    <div class="max-w-2xl"><a href="{{ route('admin.accounts.index') }}" class="text-sm font-semibold text-rose-600 hover:text-rose-700">← Kembali ke akun admin</a><h1 class="mt-5 font-serif text-3xl font-semibold tracking-tight text-stone-800">Tambah akun tim</h1><p class="mt-2 text-sm text-stone-500">Berikan akses sesuai tanggung jawab anggota tim.</p><x-card class="mt-7"><form method="POST" action="{{ route('admin.accounts.store') }}" class="grid gap-5 sm:grid-cols-2">@csrf @include('admin.accounts.partials.form', ['submitLabel' => 'Simpan akun'])</form></x-card></div>
@endsection
