@props(['title' => 'Belum ada data', 'description' => 'Data akan tampil di sini saat sudah tersedia.'])

<div {{ $attributes->class(['mx-auto flex max-w-sm flex-col items-center py-10 text-center']) }}>
    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-50 text-rose-500"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10.5v.01M12 15v.01M4.93 19.07a10 10 0 1 1 14.14 0" /><path stroke-linecap="round" d="M8.5 16.5h7" /></svg></span>
    <h3 class="mt-4 text-sm font-semibold text-stone-700">{{ $title }}</h3>
    <p class="mt-1 text-sm leading-6 text-stone-500">{{ $description }}</p>
    @isset($action)<div class="mt-4">{{ $action }}</div>@endisset
</div>
