@props(['padding' => 'p-5'])

<section {{ $attributes->class(['rounded-2xl border border-stone-100 bg-white shadow-sm shadow-stone-200/40', $padding]) }}>
    {{ $slot }}
</section>
