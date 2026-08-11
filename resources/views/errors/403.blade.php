@extends('layouts.error')

@section('title', 'Akses Ditolak')

@section('content')

    <section>
        <h1>403</h1>

        <h2>Akses Ditolak</h2>

        <p>
            Kamu tidak memiliki izin untuk mengakses halaman ini.
        </p>

        <a href="{{ route('home') }}">
            Kembali ke Beranda
        </a>
    </section>

@endsection