@extends('layouts.admin')

@section('title', 'Pesanan')
@section('page-title', 'Pesanan')

@section('content')
    <div>
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pembayaran</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Verifikasi pembayaran</h1><p class="mt-2 text-sm text-stone-500">Tinjau bukti pembayaran yang menunggu konfirmasi dari tim Anda.</p></div>

        <div class="mt-7">
            <x-data-table :headers="['Pesanan', 'Pelanggan', 'Total', 'Bukti diunggah', 'Status', 'Aksi']" :is-empty="$orders->isEmpty()" empty-title="Belum ada pembayaran menunggu" empty-description="Bukti pembayaran pelanggan yang perlu diperiksa akan muncul di sini.">
                @foreach ($orders as $order)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4"><p class="font-mono text-xs font-bold text-stone-700">{{ $order->order_number }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold text-stone-700">{{ $order->customer_name }}</p><p class="mt-0.5 text-xs text-stone-400">{{ $order->customer_phone }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-stone-700">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-500">{{ $order->latestPaymentProof?->uploaded_at?->locale('id')->translatedFormat('d M Y, H.i') }} WIB</td>
                        <td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$order->status->label()" /></td>
                        <td class="whitespace-nowrap px-5 py-4"><a href="{{ route('admin.payment-verifications.show', $order) }}" class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Periksa bukti</a></td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    </div>
@endsection
