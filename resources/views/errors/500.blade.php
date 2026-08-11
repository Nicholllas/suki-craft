@extends('layouts.error')

@section('title', 'Terjadi Kesalahan')

@section('content')

    <section>
        <h1>500</h1>

        <h2>Terjadi Kesalahan</h2>

        <p>
            Maaf, terjadi kesalahan pada sistem.
            Silakan coba beberapa saat lagi.
        </p>

        <a href="{{ route('home') }}">
            Kembali ke Beranda
        </a>
    </section>

@endsection