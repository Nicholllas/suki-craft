@props(['status'])

@php
    $status = strtolower($status);
    $styles = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'menunggu pembayaran' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'menunggu verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'pembayaran dikonfirmasi' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'sedang dirangkai' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'dalam pengiriman' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'telah diterima' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'disetujui' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'ditolak' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'diproses' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'dikirim' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'dibatalkan' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'aktif' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'nonaktif' => 'bg-stone-100 text-stone-600 ring-stone-200',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset', $styles[$status] ?? 'bg-stone-100 text-stone-600 ring-stone-200']) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $slot->isNotEmpty() ? $slot : ucfirst($status) }}
</span>
