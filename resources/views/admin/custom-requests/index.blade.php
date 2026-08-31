@extends('layouts.admin')

@section('title', 'Custom Bouquet')
@section('page-title', 'Custom Bouquet')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end"><div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Permintaan pelanggan</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Custom Bouquet</h1><p class="mt-2 text-sm text-stone-500">Tinjau detail, kirim harga, dan pantau permintaan yang telah disetujui.</p></div></div>

    <div class="mt-7 space-y-4">
        @forelse ($customRequests as $customRequest)
            <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm"><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div><p class="font-mono text-xs font-bold uppercase tracking-[0.16em] text-rose-500">{{ $customRequest->request_number }}</p><h2 class="mt-2 font-serif text-xl font-semibold text-stone-800">{{ $customRequest->customer->name }}</h2><p class="mt-1 text-sm text-stone-500">{{ $customRequest->custom_category_name ?? $customRequest->customBouquetCategory?->name ?? $customRequest->product->name }} · dibutuhkan {{ $customRequest->needed_date->locale('id')->translatedFormat('d F Y') }}</p>@if($customRequest->quoted_price !== null)<p class="mt-3 text-sm font-semibold text-stone-800">Penawaran Rp{{ number_format($customRequest->quoted_price, 0, ',', '.') }}</p>@endif</div><span class="inline-flex w-fit rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700">{{ $customRequest->status->label() }}</span></div><a href="{{ route('admin.custom-requests.show', $customRequest) }}" class="mt-5 inline-flex rounded-xl border border-rose-200 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Tinjau permintaan</a></article>
        @empty
            <x-empty-state title="Belum ada Custom Bouquet" description="Permintaan dari pelanggan akan muncul di halaman ini." />
        @endforelse
    </div>

    @if ($customRequests->hasPages())<div class="mt-7">{{ $customRequests->links() }}</div>@endif
@endsection