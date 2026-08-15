@extends('layouts.admin')

@section('title', 'Kurir')
@section('page-title', 'Kurir')

@section('content')
    <div>
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pengiriman</p>
                <h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Master kurir</h1>
                <p class="mt-2 text-sm text-stone-500">Simpan kontak kurir agar penugasan pengiriman berjalan rapi.</p>
            </div>
            <button type="button" x-on:click="$dispatch('open-modal', 'create-courier')" class="inline-flex h-11 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">+ Tambah kurir</button>
        </div>

        <div class="mt-7">
            <x-data-table :headers="['Kurir', 'Nomor WhatsApp', 'Status', 'Aksi']" :is-empty="$couriers->isEmpty()" empty-title="Belum ada kurir" empty-description="Tambahkan kurir pertama untuk mulai menugaskan pengiriman.">
                @foreach ($couriers as $courier)
                    <tr>
                        <td class="px-5 py-4"><p class="font-semibold text-stone-700">{{ $courier->name }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-500">{{ $courier->phone }}</td>
                        <td class="whitespace-nowrap px-5 py-4"><x-status-badge :status="$courier->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center gap-1">
                                <button type="button" x-on:click="$dispatch('open-modal', 'edit-courier-{{ $courier->id }}')" class="rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50">Edit</button>
                                <form method="POST" action="{{ route('admin.couriers.toggle', $courier) }}" onsubmit="return confirm('Ubah status kurir ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg px-3 py-2 text-xs font-semibold text-stone-500 transition hover:bg-stone-100">{{ $courier->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        <div class="mt-5">{{ $couriers->links() }}</div>
    </div>

    <x-modal name="create-courier" focusable>
        <form method="POST" action="{{ route('admin.couriers.store') }}" class="p-6">
            @csrf
            <h2 class="font-serif text-2xl font-semibold text-stone-800">Tambah kurir</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">Simpan nama dan nomor WhatsApp kurir untuk penugasan pengiriman.</p>
            <label for="create-courier-name" class="mt-5 block text-sm font-semibold text-stone-700">Nama kurir</label>
            <input id="create-courier-name" name="name" value="{{ old('name') }}" required class="mt-2 h-11 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200">
            @error('name')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <label for="create-courier-phone" class="mt-5 block text-sm font-semibold text-stone-700">Nomor WhatsApp</label>
            <input id="create-courier-phone" name="phone" value="{{ old('phone') }}" required class="mt-2 h-11 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200">
            @error('phone')
                <p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>
            @enderror
            <label class="mt-5 flex items-center gap-3 text-sm font-medium text-stone-700">
                <input name="is_active" type="checkbox" value="1" checked class="rounded border-stone-300 text-rose-500 focus:ring-rose-200">
                Kurir aktif dan siap ditugaskan
            </label>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-courier')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Batal</button>
                <button type="submit" class="h-11 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600">Simpan kurir</button>
            </div>
        </form>
    </x-modal>

    @foreach ($couriers as $courier)
        <x-modal name="edit-courier-{{ $courier->id }}" focusable>
            <form method="POST" action="{{ route('admin.couriers.update', $courier) }}" class="p-6">
                @csrf
                @method('PUT')
                <h2 class="font-serif text-2xl font-semibold text-stone-800">Edit kurir</h2>
                <label for="edit-courier-name-{{ $courier->id }}" class="mt-5 block text-sm font-semibold text-stone-700">Nama kurir</label>
                <input id="edit-courier-name-{{ $courier->id }}" name="name" value="{{ $courier->name }}" required class="mt-2 h-11 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200">
                <label for="edit-courier-phone-{{ $courier->id }}" class="mt-5 block text-sm font-semibold text-stone-700">Nomor WhatsApp</label>
                <input id="edit-courier-phone-{{ $courier->id }}" name="phone" value="{{ $courier->phone }}" required class="mt-2 h-11 w-full rounded-xl border-stone-200 px-4 text-sm focus:border-rose-300 focus:ring-rose-200">
                <label class="mt-5 flex items-center gap-3 text-sm font-medium text-stone-700">
                    <input name="is_active" type="checkbox" value="1" @checked($courier->is_active) class="rounded border-stone-300 text-rose-500 focus:ring-rose-200">
                    Kurir aktif dan siap ditugaskan
                </label>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-courier-{{ $courier->id }}')" class="h-11 rounded-xl px-5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Batal</button>
                    <button type="submit" class="h-11 rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white transition hover:bg-rose-600">Simpan perubahan</button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
