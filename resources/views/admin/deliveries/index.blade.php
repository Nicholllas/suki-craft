@extends('layouts.admin')

@section('title', 'Pengiriman')
@section('page-title', 'Pengiriman')

@section('content')
    <div>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Operasional</p>
                <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Jadwal pengiriman</h1>
                <p class="mt-2 text-sm text-stone-500">Atur perakitan, penugasan kurir, dan pengantaran buket setiap hari.</p>
            </div>
            <a href="{{ route('admin.couriers.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-rose-200 bg-white px-5 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Kelola kurir</a>
        </div>

        <section class="mt-7 rounded-3xl border border-stone-200 bg-white p-4 sm:p-5">
            <form class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]">
                <label>
                    <span class="sr-only">Tanggal pengiriman</span>
                    <input name="date" type="date" value="{{ $date }}" class="h-11 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                </label>
                <label>
                    <span class="sr-only">Slot waktu pengiriman</span>
                    <select name="time_slot" class="h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                        <option value="">Semua slot waktu</option>
                        @foreach ($timeSlots as $key => $label)
                            <option value="{{ $key }}" @selected($timeSlot === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="h-11 rounded-xl bg-stone-800 px-5 text-sm font-semibold text-white transition hover:bg-stone-700">Tampilkan</button>
            </form>
        </section>

        <div class="mt-7">
            <x-data-table :headers="['Pesanan', 'Penerima & Alamat', 'Slot', 'Kurir', 'Status', 'Aksi']" :is-empty="$orders->isEmpty()" empty-title="Tidak ada pengiriman terjadwal" empty-description="Pesanan yang sudah dikonfirmasi pembayarannya akan muncul di sini sesuai tanggal kirim.">
                @foreach ($orders as $order)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4">
                            <p class="font-mono text-xs font-bold text-stone-700">{{ $order->order_number }}</p>
                            <p class="mt-1 text-xs text-stone-400">Rp{{ number_format($order->total, 0, ',', '.') }}</p>
                        </td>
                        <td class="min-w-64 px-5 py-4">
                            <p class="font-semibold text-stone-700">{{ $order->customer_name }}</p>
                            <p class="mt-1 text-xs text-stone-400">{{ $order->customer_phone }}</p>
                            <p class="mt-2 max-w-sm text-xs leading-5 text-stone-500">{{ $order->delivery_address }}</p>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ $timeSlots[$order->delivery_time_slot] ?? $order->delivery_time_slot }}</td>
                        <td class="whitespace-nowrap px-5 py-4">
                            @if ($order->courier)
                                <p class="text-sm font-semibold text-stone-700">{{ $order->courier->name }}</p>
                                <p class="mt-1 text-xs text-stone-400">{{ $order->courier->phone }}</p>
                            @else
                                <span class="text-sm text-stone-400">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$order->status->label()" /></td>
                        <td class="min-w-48 px-5 py-4">
                            <div class="flex flex-wrap gap-2">
                                @if ($order->status === \App\Enums\OrderStatus::PAYMENT_CONFIRMED)
                                    <form method="POST" action="{{ route('admin.deliveries.processing', $order) }}" onsubmit="return confirm('Mulai proses perakitan buket ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-lg bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">Mulai proses</button>
                                    </form>
                                @endif

                                @if ($order->status === \App\Enums\OrderStatus::PROCESSING)
                                    <button type="button" x-on:click="$dispatch('open-modal', 'assign-courier-{{ $order->id }}')" class="rounded-lg bg-violet-50 px-3 py-2 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">{{ $order->courier ? 'Ganti kurir' : 'Tugaskan kurir' }}</button>
                                    @if ($order->courier)
                                        <form method="POST" action="{{ route('admin.deliveries.out_for_delivery', $order) }}" onsubmit="return confirm('Tandai pesanan ini sedang dalam pengiriman?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-lg bg-violet-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-violet-700">Mulai antar</button>
                                        </form>
                                    @endif
                                @endif

                                @if ($order->status === \App\Enums\OrderStatus::OUT_FOR_DELIVERY)
                                    <button type="button" x-on:click="$dispatch('open-modal', 'mark-delivered-{{ $order->id }}')" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">Tandai terkirim</button>
                                @endif

                                <button type="button" x-on:click="$dispatch('open-modal', 'cancel-delivery-{{ $order->id }}')" class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Batalkan</button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        <div class="mt-5">{{ $orders->links() }}</div>
    </div>

    @foreach ($orders as $order)
        <x-modal name="assign-courier-{{ $order->id }}" focusable>
            <form method="POST" action="{{ route('admin.deliveries.courier.assign', $order) }}" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="font-serif text-2xl font-semibold text-stone-800">Tugaskan kurir</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Pilih kurir untuk pesanan {{ $order->order_number }}.</p>
                @if ($couriers->isNotEmpty())
                    <label for="courier-id-{{ $order->id }}" class="mt-5 block text-sm font-semibold text-stone-700">Kurir aktif</label>
                    <select id="courier-id-{{ $order->id }}" name="courier_id" required class="mt-2 h-11 w-full rounded-xl border-stone-200 bg-white px-3 text-sm text-stone-700 focus:border-rose-300 focus:ring-rose-200">
                        <option value="">Pilih kurir</option>
                        @foreach ($couriers as $courier)
                            <option value="{{ $courier->id }}" @selected($order->courier_id === $courier->id)>{{ $courier->name }} · {{ $courier->phone }}</option>
                        @endforeach
                    </select>
                    @error('courier_id')
                        <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" x-on:click="$dispatch('close-modal', 'assign-courier-{{ $order->id }}')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Batal</button>
                        <button type="submit" class="h-11 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600">Tugaskan kurir</button>
                    </div>
                @else
                    <p class="mt-5 rounded-2xl bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800">Belum ada kurir aktif. Tambahkan atau aktifkan kurir terlebih dahulu.</p>
                    <div class="mt-6 flex justify-end"><button type="button" x-on:click="$dispatch('close-modal', 'assign-courier-{{ $order->id }}')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Tutup</button></div>
                @endif
            </form>
        </x-modal>

        <x-modal name="mark-delivered-{{ $order->id }}" focusable>
            <form method="POST" action="{{ route('admin.deliveries.delivered', $order) }}" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="font-serif text-2xl font-semibold text-stone-800">Tandai pesanan terkirim</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Simpan foto bukti pengiriman bila tersedia. Foto akan ditampilkan kepada pelanggan.</p>
                <label for="proof-photo-{{ $order->id }}" class="mt-5 block text-sm font-semibold text-stone-700">Foto bukti pengiriman <span class="font-normal text-stone-400">(opsional)</span></label>
                <input id="proof-photo-{{ $order->id }}" name="proof_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-stone-200 bg-white text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-rose-700 hover:file:bg-rose-100">
                <p class="mt-2 text-xs text-stone-400">JPG, PNG, atau WEBP maksimal 5 MB.</p>
                @error('proof_photo')
                    <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" x-on:click="$dispatch('close-modal', 'mark-delivered-{{ $order->id }}')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Batal</button>
                    <button type="submit" class="h-11 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white transition hover:bg-emerald-700">Konfirmasi terkirim</button>
                </div>
            </form>
        </x-modal>

        <x-modal name="cancel-delivery-{{ $order->id }}" focusable>
            <form method="POST" action="{{ route('admin.deliveries.cancel', $order) }}" class="p-6">
                @csrf
                @method('PATCH')
                <h2 class="font-serif text-2xl font-semibold text-stone-800">Batalkan pesanan</h2>
                <p class="mt-2 text-sm leading-6 text-stone-500">Alasan akan terlihat oleh pelanggan di halaman tracking pesanan.</p>
                <label for="cancel-reason-{{ $order->id }}" class="mt-5 block text-sm font-semibold text-stone-700">Alasan pembatalan</label>
                <textarea id="cancel-reason-{{ $order->id }}" name="reason" rows="4" required class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-800 focus:border-rose-300 focus:ring-rose-200" placeholder="Contoh: Bunga yang dipilih sedang tidak tersedia.">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
                @enderror
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" x-on:click="$dispatch('close-modal', 'cancel-delivery-{{ $order->id }}')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Kembali</button>
                    <button type="submit" class="h-11 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600">Batalkan pesanan</button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
