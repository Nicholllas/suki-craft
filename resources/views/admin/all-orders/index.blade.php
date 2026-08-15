@extends('layouts.admin')

@section('title', 'Semua Pesanan')
@section('page-title', 'Semua Pesanan')

@section('content')
    <div>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Operasional</p>
                <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Semua pesanan</h1>
                <p class="mt-2 text-sm text-stone-500">Pantau seluruh perjalanan pesanan, dari pembayaran sampai pesanan selesai.</p>
            </div>
            <a href="{{ route('admin.payment-verifications.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-5 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">Verifikasi pembayaran</a>
        </div>

        <div class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($summaries as $summary)
                <x-card class="flex items-center justify-between gap-4" padding="p-4">
                    <div>
                        <p class="text-xs font-semibold text-stone-500">{{ $summary['label'] }}</p>
                        <p class="mt-1 font-serif text-3xl font-semibold tracking-tight text-stone-800">{{ number_format($summary['value']) }}</p>
                    </div>
                    <span @class([
                        'grid h-10 w-10 place-items-center rounded-2xl text-sm font-bold' => true,
                        'bg-amber-50 text-amber-700' => in_array($summary['key'], ['pending_payment', 'awaiting_verification'], true),
                        'bg-sky-50 text-sky-700' => $summary['key'] === 'processing',
                        'bg-violet-50 text-violet-700' => $summary['key'] === 'out_for_delivery',
                        'bg-emerald-50 text-emerald-700' => $summary['key'] === 'delivered_today',
                        'bg-rose-50 text-rose-700' => $summary['key'] === 'cancelled',
                    ])>{{ $summary['value'] }}</span>
                </x-card>
            @endforeach
        </div>

        <x-card class="mt-6" padding="p-5">
            <form action="{{ route('admin.orders.index') }}" class="space-y-5">
                <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                    <div class="xl:col-span-2">
                        <label for="search" class="text-xs font-semibold text-stone-600">Cari pesanan</label>
                        <input id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nomor pesanan, nama, atau nomor WhatsApp" class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200">
                    </div>
                    <div>
                        <label for="order_date_from" class="text-xs font-semibold text-stone-600">Pesanan dibuat, dari</label>
                        <input id="order_date_from" name="order_date_from" type="date" value="{{ $filters['order_date_from'] ?? '' }}" class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                    </div>
                    <div>
                        <label for="order_date_to" class="text-xs font-semibold text-stone-600">Pesanan dibuat, sampai</label>
                        <input id="order_date_to" name="order_date_to" type="date" value="{{ $filters['order_date_to'] ?? '' }}" class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                        @error('order_date_to')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="delivery_date_from" class="text-xs font-semibold text-stone-600">Pengiriman, dari</label>
                        <input id="delivery_date_from" name="delivery_date_from" type="date" value="{{ $filters['delivery_date_from'] ?? '' }}" class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                    </div>
                    <div>
                        <label for="delivery_date_to" class="text-xs font-semibold text-stone-600">Pengiriman, sampai</label>
                        <input id="delivery_date_to" name="delivery_date_to" type="date" value="{{ $filters['delivery_date_to'] ?? '' }}" class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                        @error('delivery_date_to')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <fieldset>
                    <legend class="text-xs font-semibold text-stone-600">Status pesanan</legend>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <a href="{{ route('admin.orders.index', request()->except(['page', 'statuses'])) }}" @class([
                            'inline-flex min-h-10 items-center rounded-xl border px-3 text-xs font-semibold transition' => true,
                            'border-rose-200 bg-rose-50 text-rose-700' => empty($filters['statuses']),
                            'border-stone-200 bg-white text-stone-600 hover:border-rose-200' => ! empty($filters['statuses']),
                        ])>Semua</a>
                        @foreach ($statusOptions as $status)
                            <label class="cursor-pointer">
                                <input name="statuses[]" type="checkbox" value="{{ $status->value }}" @checked(in_array($status->value, $filters['statuses'] ?? [], true)) class="peer sr-only">
                                <span class="inline-flex min-h-10 items-center rounded-xl border border-stone-200 bg-white px-3 text-xs font-semibold text-stone-600 transition peer-checked:border-rose-200 peer-checked:bg-rose-50 peer-checked:text-rose-700 hover:border-rose-200">{{ $status->label() }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('statuses.*')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
                </fieldset>

                <div class="flex flex-col-reverse gap-3 border-t border-stone-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.orders.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Reset filter</a>
                    <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-stone-800 px-5 text-sm font-semibold text-white transition hover:bg-stone-700">Terapkan filter</button>
                </div>
            </form>
        </x-card>

        <div class="mt-6">
            <x-data-table :headers="['Pesanan', 'Pelanggan', 'Dibuat', 'Pengiriman', 'Total', 'Status', 'Aksi']" :is-empty="$orders->isEmpty()" empty-title="Pesanan tidak ditemukan" empty-description="Ubah atau reset filter untuk melihat pesanan yang tersedia.">
                @foreach ($orders as $order)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4"><p class="font-mono text-xs font-bold text-stone-700">{{ $order->order_number }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold text-stone-700">{{ $order->customer_name }}</p><p class="mt-0.5 text-xs text-stone-400">{{ $order->customer_phone }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4"><p class="text-sm text-stone-600">{{ $order->created_at->locale('id')->translatedFormat('d M Y') }}</p><p class="mt-0.5 text-xs text-stone-400">{{ $order->created_at->format('H.i') }} WIB</p></td>
                        <td class="px-5 py-4"><p class="whitespace-nowrap text-sm font-medium text-stone-700">{{ $order->delivery_date->locale('id')->translatedFormat('d M Y') }}</p><p class="mt-0.5 whitespace-nowrap text-xs text-stone-400">{{ config('delivery.time_slots.'.$order->delivery_time_slot, $order->delivery_time_slot) }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-stone-700">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$order->status->label()" /></td>
                        <td class="whitespace-nowrap px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="inline-flex rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Lihat detail</a></td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    </div>
@endsection
