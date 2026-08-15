@extends('layouts.store')

@section('title', 'Koleksi Buket | Suki Craft')
@section('description', 'Temukan buket bunga pilihan dari Suki Craft untuk setiap momen istimewa.')

@section('content')

<div class="container py-5">

    <div class="text-center mb-5">

        <h1>
            Koleksi Bunga
        </h1>

        <p class="text-muted">
            Pilih bunga terbaik untuk orang tersayang.
        </p>

    </div>

    <form
        method="GET"
        action="{{ route('products.index') }}"
        class="row g-2 mb-4">

        <div class="col-md-5">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Cari produk..."
                value="{{ request('search') }}">

        </div>

        <div class="col-md-4">

            <select
                name="category"
                class="form-select">

                <option value="">
                    Semua Kategori
                </option>

                @foreach($categories as $category)

                    <option
                        value="{{ $category->slug }}"
                        @selected(request('category') === $category->slug)>
                        {{ $category->name }}
                    </option>

                @endforeach

            </select>

        </div>

        <div class="col-md-3">

            <button class="btn btn-primary w-100">
                Cari Produk
            </button>

        </div>

    </form>

    <div class="row g-4">

        @forelse($products as $product)

            <div class="col-6 col-md-4 col-lg-3">

                <div class="card h-100">

                    @if($product->image)

                        <img
                            src="{{ asset('storage/' . $product->image) }}"
                            class="card-img-top"
                            style="height: 250px; object-fit: cover;"
                            alt="{{ $product->name }}">

                    @endif

                    <div class="card-body">

                        <small class="text-muted">
                            {{ $product->category->name }}
                        </small>

                        <h5 class="card-title mt-1">
                            {{ $product->name }}
                        </h5>

                        <p class="fw-bold">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </p>

                        <a
                            href="{{ route('products.show', $product->slug) }}"
                            class="btn btn-outline-primary w-100">
                            Lihat Produk
                        </a>

                    </div>

                </div>

            </div>

        @empty

            <div class="col-12">

                <div class="text-center py-5">
                    Produk tidak ditemukan.
                </div>

            </div>

        @endforelse

    </div>

    <div class="mt-5">
        {{ $products->links() }}
    </div>

</div>

@endsection
