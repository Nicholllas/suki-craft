@props(['headers' => [], 'isEmpty' => false, 'emptyTitle' => 'Belum ada data', 'emptyDescription' => 'Data akan tampil di sini saat sudah tersedia.'])

<div {{ $attributes->class(['overflow-hidden rounded-2xl border border-stone-100 bg-white shadow-sm shadow-stone-200/40']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-stone-100 bg-stone-50/70 text-xs font-semibold uppercase tracking-wide text-stone-500"><tr>@foreach($headers as $header)<th scope="col" class="whitespace-nowrap px-5 py-3.5">{{ $header }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-stone-100 text-stone-600">
                @if($isEmpty)
                    <tr><td colspan="{{ count($headers) }}"><x-empty-state :title="$emptyTitle" :description="$emptyDescription" /></td></tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
</div>
