@extends('layouts.admin')

@section('title', 'Tambah Bahan')
@section('page-title', 'Tambah Bahan')

@section('content')
    <form method="POST" action="{{ route('admin.ingredients.store') }}">@csrf @include('admin.ingredients.partials.form', ['submitLabel' => 'Simpan bahan'])</form>
@endsection
