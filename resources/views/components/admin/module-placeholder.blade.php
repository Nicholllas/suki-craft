@props(['title', 'description'])

<div class="max-w-3xl">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Dalam pengembangan</p><h1 class="mt-2 font-serif text-3xl font-semibold tracking-tight text-stone-800 sm:text-4xl">{{ $title }}</h1><p class="mt-3 max-w-xl leading-7 text-stone-500">{{ $description }}</p></div>
    <x-card class="mt-8" padding="p-2"><x-empty-state title="Belum ada data untuk ditampilkan" description="Modul ini telah disiapkan dan siap dihubungkan ke data operasional Suki Craft." /></x-card>
</div>
