@extends('layouts.admin')

@section('title', 'Tambah Produk Buket')
@section('page-title', 'Produk Buket')

@section('content')
    <div class="max-w-5xl"><a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-rose-600">← Kembali ke produk</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Tambah produk buket</h1><p class="mt-2 text-sm text-stone-500">Lengkapi informasi, opsi varian, dan minimal satu foto produk.</p><form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-7">@csrf @include('admin.products.partials.form', ['submitLabel' => 'Simpan produk'])</form></div>
@endsection
