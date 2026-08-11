@extends('layouts.error')

@section('title', 'Sistem Sedang Dalam Pemeliharaan')

@section('content')

    <section>
        <h1>503</h1>

        <h2>Sistem Sedang Dalam Pemeliharaan</h2>

        <p>
            Suki Craft sedang melakukan pemeliharaan.
            Silakan coba kembali beberapa saat lagi.
        </p>

        <a href="{{ route('home') }}">
            Coba Lagi
        </a>
    </section>

@endsection