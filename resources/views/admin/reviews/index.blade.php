@extends('layouts.admin')

@section('title', 'Moderasi Ulasan')
@section('page-title', 'Moderasi Ulasan')

@section('content')
    <div>
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Pelanggan</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800">Moderasi ulasan</h1><p class="mt-2 text-sm text-stone-500">Tinjau pengalaman pelanggan sebelum ditampilkan pada halaman produk.</p></div>

        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mt-6 flex flex-wrap gap-2">
            @foreach(['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'all' => 'Semua'] as $value => $label)
                <button type="submit" name="status" value="{{ $value }}" class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $status === $value ? 'bg-rose-500 text-white' : 'bg-white text-stone-600 ring-1 ring-stone-200 hover:bg-rose-50 hover:text-rose-600' }}">{{ $label }}</button>
            @endforeach
        </form>

        <div class="mt-7">
            <x-data-table :headers="['Produk', 'Reviewer', 'Rating', 'Ulasan', 'Tanggal', 'Status', 'Aksi']" :is-empty="$reviews->isEmpty()" empty-title="Tidak ada ulasan" empty-description="Ulasan yang sesuai dengan filter akan muncul di sini.">
                @foreach($reviews as $review)
                    <tr x-data="{ rejectOpen: false }">
                        <td class="px-5 py-4"><p class="font-semibold text-stone-700">{{ $review->product?->name ?? 'Produk tidak tersedia' }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4"><p class="font-semibold text-stone-700">{{ $review->reviewer_name }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4"><span class="font-semibold text-amber-500">{{ str_repeat('★', $review->rating) }}</span><span class="text-stone-300">{{ str_repeat('☆', 5 - $review->rating) }}</span></td>
                        <td class="min-w-64 px-5 py-4"><p class="text-sm leading-6 text-stone-600">{{ $review->comment ?: 'Tanpa komentar' }}</p>@if($review->photo_path)<a href="{{ Storage::url($review->photo_path) }}" target="_blank" rel="noopener noreferrer" class="mt-3 block"><img src="{{ Storage::url($review->photo_path) }}" alt="Foto ulasan {{ $review->reviewer_name }}" class="h-16 w-16 rounded-xl object-cover ring-1 ring-stone-200"></a>@endif</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-500">{{ $review->created_at->locale('id')->translatedFormat('d M Y, H.i') }} WIB</td>
                        <td class="whitespace-nowrap px-5 py-4"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $review->status->value === 'approved' ? 'bg-emerald-50 text-emerald-700' : ($review->status->value === 'rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ $review->status->label() }}</span>@if($review->admin_note)<p class="mt-2 max-w-48 text-xs leading-5 text-rose-600">{{ $review->admin_note }}</p>@endif</td>
                        <td class="whitespace-nowrap px-5 py-4">@if($review->status->value === 'pending')<div class="flex items-center gap-2"><form method="POST" action="{{ route('admin.reviews.approve', $review) }}">@csrf @method('PATCH')<button type="submit" class="rounded-lg bg-emerald-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-600">Setujui</button></form><button type="button" @click="rejectOpen = true" class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Tolak</button></div><div x-cloak x-show="rejectOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-stone-900/50 p-4" @keydown.escape.window="rejectOpen = false"><div @click.outside="rejectOpen = false" class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl"><p class="font-serif text-2xl font-semibold text-stone-800">Tolak ulasan</p><p class="mt-2 text-sm leading-6 text-stone-500">Berikan alasan agar keputusan moderasi tercatat dengan jelas.</p><form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="mt-5">@csrf @method('PATCH')<label for="reason-{{ $review->id }}" class="text-sm font-semibold text-stone-800">Alasan penolakan</label><textarea id="reason-{{ $review->id }}" name="reason" required rows="4" maxlength="1000" class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm focus:border-rose-300 focus:ring-rose-200"></textarea><div class="mt-4 flex justify-end gap-3"><button type="button" @click="rejectOpen = false" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-500 hover:bg-stone-100">Batal</button><button type="submit" class="rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">Tolak ulasan</button></div></form></div></div>@else<span class="text-xs text-stone-400">Sudah dimoderasi</span>@endif</td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        @if($reviews->hasPages())<div class="mt-5">{{ $reviews->links() }}</div>@endif
    </div>
@endsection
