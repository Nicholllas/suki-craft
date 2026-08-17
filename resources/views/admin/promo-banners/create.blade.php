@extends('layouts.admin')
@section('title', 'Tambah Banner Promo')
@section('page-title', 'Banner Promo')
@section('content')
<div class="max-w-2xl"><a href="{{ route('admin.promo-banners.index') }}" class="text-sm font-semibold text-rose-600">← Kembali</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Tambah banner promo</h1><form method="POST" action="{{ route('admin.promo-banners.store') }}" enctype="multipart/form-data" class="mt-7 space-y-5">@csrf @include('admin.promo-banners.form', ['promoBanner' => null, 'submitLabel' => 'Simpan banner'])</form></div>
@endsection
