@props(['label', 'value', 'detail' => null, 'tone' => 'rose'])

@php
    $tones = [
        'rose' => 'bg-rose-50 text-rose-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ];
@endphp

<x-card class="relative overflow-hidden" padding="p-5">
    <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full {{ $tones[$tone] ?? $tones['rose'] }} opacity-30"></div>
    <div class="relative flex items-start justify-between gap-3">
        <div><p class="text-sm font-medium text-stone-500">{{ $label }}</p><p class="mt-2 text-2xl font-semibold tracking-tight text-stone-800">{{ $value }}</p>@if($detail)<p class="mt-2 text-xs text-stone-400">{{ $detail }}</p>@endif</div>
        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['rose'] }}">{{ $icon }}</span>
    </div>
</x-card>
