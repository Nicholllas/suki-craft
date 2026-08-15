@props(['active' => false])

<a {{ $attributes->class(['flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition', 'bg-rose-50 text-rose-700 shadow-sm shadow-rose-100' => $active, 'text-stone-600 hover:bg-stone-100 hover:text-stone-900' => ! $active]) }}>
    {{ $slot }}
</a>
