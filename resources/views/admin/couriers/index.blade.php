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
                                <form method="POST" action="{{ route('admin.couriers.toggle', $courier) }}" data-confirm="Status kurir akan diperbarui." data-confirm-button="Ya, ubah status" data-confirm-title="Ubah status kurir?">
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

    <x-modal name="create-courier" maxWidth="md" focusable>
        <form method="POST" action="{{ route('admin.couriers.store') }}">
            @csrf
            <div class="border-b border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50/60 px-6 py-6 sm:px-7">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3.5">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-500 text-white shadow-sm shadow-rose-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm10.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM8.25 18.75h7.5M2.25 4.5h3l2.4 10.2a1.5 1.5 0 0 0 1.46 1.16h7.83a1.5 1.5 0 0 0 1.46-1.16l.69-2.95H6.43" /></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pengiriman</p>
                            <h2 class="mt-1 font-serif text-2xl font-semibold tracking-tight text-stone-800">Tambah kurir</h2>
                            <p class="mt-1 text-sm leading-6 text-stone-500">Simpan kontak kurir untuk penugasan pengiriman.</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'create-courier')" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-stone-400 transition hover:bg-white hover:text-stone-700" aria-label="Tutup modal tambah kurir">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                    </button>
                </div>
            </div>

            <div class="space-y-5 px-6 py-6 sm:px-7">
                <div class="space-y-2">
                    <label for="create-courier-name" class="block text-sm font-semibold text-stone-700">Nama kurir</label>
                    <input id="create-courier-name" name="name" value="{{ old('name') }}" autocomplete="name" required class="h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 shadow-sm transition placeholder:text-stone-400 focus:border-rose-300 focus:ring-4 focus:ring-rose-100" placeholder="Contoh: Andi Pratama">
                    @error('name')
                        <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="space-y-2">
                    <label for="create-courier-phone" class="block text-sm font-semibold text-stone-700">Nomor WhatsApp</label>
                    <input id="create-courier-phone" name="phone" value="{{ old('phone') }}" type="tel" inputmode="tel" autocomplete="tel" required class="h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 shadow-sm transition placeholder:text-stone-400 focus:border-rose-300 focus:ring-4 focus:ring-rose-100" placeholder="Contoh: 0812 3456 7890">
                    @error('phone')
                        <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 bg-stone-50/80 p-4 text-sm text-stone-600 transition hover:border-rose-200 hover:bg-rose-50/40">
                    <input name="is_active" type="checkbox" value="1" checked class="mt-0.5 h-4.5 w-4.5 rounded border-stone-300 text-rose-500 focus:ring-rose-200">
                    <span><span class="block font-semibold text-stone-700">Kurir aktif dan siap ditugaskan</span><span class="mt-0.5 block text-xs leading-5 text-stone-500">Kurir aktif dapat dipilih saat mengatur jadwal pengiriman.</span></span>
                </label>
            </div>

            <div class="grid gap-3 border-t border-stone-100 bg-stone-50/80 px-6 py-4 sm:grid-cols-[1fr_auto] sm:px-7">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-courier')" class="inline-flex h-12 items-center justify-center rounded-xl border border-stone-200 bg-white px-5 text-sm font-semibold text-stone-600 transition hover:bg-stone-100">Batal</button>
                <button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:bg-rose-600 focus:outline-none focus:ring-4 focus:ring-rose-100">Simpan kurir</button>
            </div>
        </form>
    </x-modal>

    @foreach ($couriers as $courier)
        <x-modal name="edit-courier-{{ $courier->id }}" maxWidth="md" focusable>
            <form method="POST" action="{{ route('admin.couriers.update', $courier) }}">
                @csrf
                @method('PUT')
                <div class="border-b border-rose-100 bg-gradient-to-br from-rose-50 via-white to-amber-50/60 px-6 py-6 sm:px-7">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3.5">
                            <div class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl bg-rose-500 text-white shadow-sm shadow-rose-200">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Zm10.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM8.25 18.75h7.5M2.25 4.5h3l2.4 10.2a1.5 1.5 0 0 0 1.46 1.16h7.83a1.5 1.5 0 0 0 1.46-1.16l.69-2.95H6.43" /></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Pengiriman</p>
                                <h2 class="mt-1 font-serif text-2xl font-semibold tracking-tight text-stone-800">Edit kurir</h2>
                                <p class="mt-1 text-sm leading-6 text-stone-500">Perbarui detail kontak dan kesiapan kurir.</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="$dispatch('close-modal', 'edit-courier-{{ $courier->id }}')" class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-stone-400 transition hover:bg-white hover:text-stone-700" aria-label="Tutup modal edit kurir">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18" /></svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-5 px-6 py-6 sm:px-7">
                    <div class="space-y-2">
                        <label for="edit-courier-name-{{ $courier->id }}" class="block text-sm font-semibold text-stone-700">Nama kurir</label>
                        <input id="edit-courier-name-{{ $courier->id }}" name="name" value="{{ $courier->name }}" autocomplete="name" required class="h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 shadow-sm transition placeholder:text-stone-400 focus:border-rose-300 focus:ring-4 focus:ring-rose-100">
                        @error('name')
                            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="space-y-2">
                        <label for="edit-courier-phone-{{ $courier->id }}" class="block text-sm font-semibold text-stone-700">Nomor WhatsApp</label>
                        <input id="edit-courier-phone-{{ $courier->id }}" name="phone" value="{{ $courier->phone }}" type="tel" inputmode="tel" autocomplete="tel" required class="h-12 w-full rounded-xl border-stone-200 bg-white px-4 text-sm text-stone-800 shadow-sm transition placeholder:text-stone-400 focus:border-rose-300 focus:ring-4 focus:ring-rose-100">
                        @error('phone')
                            <p class="text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-stone-200 bg-stone-50/80 p-4 text-sm text-stone-600 transition hover:border-rose-200 hover:bg-rose-50/40">
                        <input name="is_active" type="checkbox" value="1" @checked($courier->is_active) class="mt-0.5 h-4.5 w-4.5 rounded border-stone-300 text-rose-500 focus:ring-rose-200">
                        <span><span class="block font-semibold text-stone-700">Kurir aktif dan siap ditugaskan</span><span class="mt-0.5 block text-xs leading-5 text-stone-500">Kurir aktif dapat dipilih saat mengatur jadwal pengiriman.</span></span>
                    </label>
                </div>

                <div class="grid gap-3 border-t border-stone-100 bg-stone-50/80 px-6 py-4 sm:grid-cols-[1fr_auto] sm:px-7">
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-courier-{{ $courier->id }}')" class="inline-flex h-12 items-center justify-center rounded-xl border border-stone-200 bg-white px-5 text-sm font-semibold text-stone-600 transition hover:bg-stone-100">Batal</button>
                    <button type="submit" class="inline-flex h-12 items-center justify-center rounded-xl bg-rose-500 px-5 text-sm font-semibold text-white shadow-sm shadow-rose-200 transition hover:bg-rose-600 focus:outline-none focus:ring-4 focus:ring-rose-100">Simpan perubahan</button>
                </div>
            </form>
        </x-modal>
    @endforeach
@endsection
