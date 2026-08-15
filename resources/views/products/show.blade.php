@extends('layouts.app')

@section('content')

<div class="container py-5">

    <div class="row g-5">

        <div class="col-md-6">

            @if($product->image)

                <img
                    src="{{ asset('storage/' . $product->image) }}"
                    class="img-fluid rounded"
                    alt="{{ $product->name }}">

            @else

                <div class="bg-light rounded p-5 text-center">
                    Tidak ada gambar.
                </div>

            @endif

        </div>

        <div class="col-md-6">

            <small class="text-muted">
                {{ $product->category->name }}
            </small>

            <h1 class="mt-2">
                {{ $product->name }}
            </h1>

            <h3 class="mt-3">
                Rp {{ number_format($product->price, 0, ',', '.') }}
            </h3>

            <div class="mt-4">

                {!! nl2br(e($product->description)) !!}

            </div>

            <div class="mt-4">

                @if($product->stock > 0)

                    <span class="badge bg-success">
                        Tersedia
                    </span>

                @else

                    <span class="badge bg-danger">
                        Stok Habis
                    </span>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection