@extends('layouts.admin')
@section('title', 'Edit Banner Promo')
@section('page-title', 'Banner Promo')
@section('content')
<div class="max-w-2xl"><a href="{{ route('admin.promo-banners.index') }}" class="text-sm font-semibold text-rose-600">← Kembali</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Edit banner promo</h1><form method="POST" action="{{ route('admin.promo-banners.update', $promoBanner) }}" enctype="multipart/form-data" class="mt-7 space-y-5">@csrf @method('PUT') @include('admin.promo-banners.form', ['submitLabel' => 'Simpan perubahan'])</form></div>
@endsection
