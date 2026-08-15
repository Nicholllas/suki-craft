@extends('layouts.admin')

@section('title', 'Edit Produk Buket')
@section('page-title', 'Produk Buket')

@section('content')
    <div class="max-w-5xl"><a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-rose-600">← Kembali ke produk</a><h1 class="mt-5 font-serif text-3xl font-semibold text-stone-800">Edit {{ $product->name }}</h1><p class="mt-2 text-sm text-stone-500">Perbarui detail, varian, dan galeri foto produk.</p><form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="mt-7">@csrf @method('PUT') @include('admin.products.partials.form', ['submitLabel' => 'Simpan perubahan'])</form>@foreach($product->images as $image)<form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" class="hidden">@csrf @method('DELETE')</form>@endforeach</div>
@endsection
