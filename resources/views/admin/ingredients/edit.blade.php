@extends('layouts.admin')

@section('title', 'Edit Bahan')
@section('page-title', 'Edit Bahan')

@section('content')
    <form method="POST" action="{{ route('admin.ingredients.update', $ingredient) }}">@csrf @method('PUT') @include('admin.ingredients.partials.form', ['submitLabel' => 'Simpan perubahan'])</form>
@endsection
