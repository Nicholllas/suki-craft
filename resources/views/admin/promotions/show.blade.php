@extends('layouts.admin')
@section('title', 'Statistik Promo')
@section('page-title', 'Promosi')
@section('content')
<div><a href="{{ route('admin.promotions.index') }}" class="text-sm font-semibold text-rose-600">← Kembali</a><h1 class="mt-4 font-serif text-3xl font-semibold">{{ $promotion->code }}</h1><p class="mt-2 text-stone-500">Total diskon diberikan: <strong>Rp{{ number_format($totalDiscount, 0, ',', '.') }}</strong></p><div class="mt-6"><x-data-table :headers="['Pesanan', 'Nomor WA', 'Diskon', 'Dipakai']" :is-empty="$usages->isEmpty()" empty-title="Belum dipakai" empty-description="Belum ada order yang menggunakan promo ini.">@foreach($usages as $usage)<tr><td class="px-5 py-4">{{ $usage->order->order_number }}</td><td class="px-5 py-4">{{ $usage->customer_phone }}</td><td class="px-5 py-4">Rp{{ number_format($usage->order->discount_amount, 0, ',', '.') }}</td><td class="px-5 py-4">{{ $usage->created_at->format('d M Y H:i') }}</td></tr>@endforeach</x-data-table></div></div>
@endsection
