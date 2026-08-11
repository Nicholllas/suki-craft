@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')

    <div>

        <div class="mb-6">

            <h2 class="text-2xl font-bold">
                Dashboard
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Selamat datang kembali di Suki Craft Admin.
            </p>

        </div>


        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="rounded-xl border bg-white p-6">
                <p class="text-sm text-gray-500">
                    Products
                </p>

                <p class="mt-2 text-2xl font-bold">
                    -
                </p>
            </div>


            <div class="rounded-xl border bg-white p-6">
                <p class="text-sm text-gray-500">
                    Orders
                </p>

                <p class="mt-2 text-2xl font-bold">
                    -
                </p>
            </div>


            <div class="rounded-xl border bg-white p-6">
                <p class="text-sm text-gray-500">
                    Customers
                </p>

                <p class="mt-2 text-2xl font-bold">
                    -
                </p>
            </div>


            <div class="rounded-xl border bg-white p-6">
                <p class="text-sm text-gray-500">
                    Revenue
                </p>

                <p class="mt-2 text-2xl font-bold">
                    -
                </p>
            </div>

        </div>

    </div>

@endsection