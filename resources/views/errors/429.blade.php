@extends('layouts.error')

@section('title', 'Terlalu Banyak Permintaan')

@section('content')

    <section>
        <h1>429</h1>

        <h2>Terlalu Banyak Permintaan</h2>

        <p>
            Kamu melakukan terlalu banyak permintaan.
            Silakan tunggu beberapa saat sebelum mencoba lagi.
        </p>

        <a href="{{ route('home') }}">
            Kembali ke Beranda
        </a>
    </section>

@endsection