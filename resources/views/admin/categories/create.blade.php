@extends('layouts.admin')

@section('title', 'Tambah Kategori')
@section('page-title', 'Kategori Produk')

@section('content')
    <div class="max-w-xl"><a href="{{ route('admin.categories.index') }}" class="text-sm font-semibold text-rose-600">← Kembali ke kategori</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Tambah kategori</h1><x-card class="mt-7"><form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-5">@csrf @include('admin.categories.partials.form', ['submitLabel' => 'Simpan kategori'])</form></x-card></div>
@endsection
