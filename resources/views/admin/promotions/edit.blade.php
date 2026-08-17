@extends('layouts.admin')
@section('title', 'Edit Promo')
@section('page-title', 'Promosi')
@section('content')
<div class="max-w-3xl"><a href="{{ route('admin.promotions.index') }}" class="text-sm font-semibold text-rose-600">← Kembali</a><h1 class="mt-4 font-serif text-3xl font-semibold">Edit {{ $promotion->code }}</h1><form method="POST" action="{{ route('admin.promotions.update', $promotion) }}" class="mt-6 grid gap-4 sm:grid-cols-2">@csrf @method('PUT') @include('admin.promotions.form', ['submitLabel' => 'Simpan perubahan'])</form></div>
@endsection
