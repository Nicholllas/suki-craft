@extends('layouts.error')

@section('title', 'Sesi Berakhir')

@section('content')

    <section>
        <h1>419</h1>

        <h2>Sesi Telah Berakhir</h2>

        <p>
            Sesi kamu telah berakhir.
            Silakan muat ulang halaman dan coba lagi.
        </p>

        <a href="{{ url()->previous() }}">
            Kembali
        </a>
    </section>

@endsection