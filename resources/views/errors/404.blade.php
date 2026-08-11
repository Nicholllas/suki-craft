@extends('layouts.error')

@section('title', 'Halaman Tidak Ditemukan')

@section('content')

    <section>
        <h1>404</h1>

        <h2>Halaman Tidak Ditemukan</h2>

        <p>
            Maaf, halaman yang kamu cari tidak tersedia.
        </p>

        <a href="{{ route('home') }}">
            Kembali ke Beranda
        </a>
    </section>

@endsection