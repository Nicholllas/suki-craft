@extends('layouts.admin')
@section('title', 'Tambah Promo')
@section('page-title', 'Promosi')
@section('content')
<div class="max-w-3xl"><a href="{{ route('admin.promotions.index') }}" class="text-sm font-semibold text-rose-600">← Kembali</a><h1 class="mt-4 font-serif text-3xl font-semibold">Tambah kode promo</h1><form method="POST" action="{{ route('admin.promotions.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf @include('admin.promotions.form', ['promotion' => null, 'submitLabel' => 'Simpan promo'])</form></div>
@endsection
