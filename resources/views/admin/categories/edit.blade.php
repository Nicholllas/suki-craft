@extends('layouts.admin')

@section('title', 'Edit Kategori')
@section('page-title', 'Kategori Produk')

@section('content')
    <div class="max-w-xl"><a href="{{ route('admin.categories.index') }}" class="text-sm font-semibold text-rose-600">← Kembali ke kategori</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Edit kategori</h1><x-card class="mt-7"><form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-5">@csrf @method('PUT') @include('admin.categories.partials.form', ['submitLabel' => 'Simpan perubahan'])</form></x-card></div>
@endsection
