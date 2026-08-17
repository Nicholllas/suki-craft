@extends('layouts.store')

@section('title', 'Suki Craft | Buket Bunga untuk Setiap Cerita')
@section('description', 'Temukan buket bunga pilihan yang dirangkai dengan penuh cinta untuk setiap momen spesial Anda.')

@section('content')
    @if ($promoBanners->isNotEmpty())
        <section class="bg-[#fffaf5] px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8" x-data="{ active: 0, total: {{ $promoBanners->count() }}, next() { this.active = (this.active + 1) % this.total }, previous() { this.active = (this.active - 1 + this.total) % this.total } }" x-init="if (total > 1) { setInterval(() => next(), 6000) }">
            <div class="relative mx-auto max-w-7xl overflow-hidden rounded-3xl shadow-lg shadow-rose-900/5">
                @foreach ($promoBanners as $index => $promoBanner)
                    <a href="{{ $promoBanner->link_url ?: route('products.index') }}" x-show="active === {{ $index }}" x-transition.opacity class="relative block aspect-[16/7] min-h-48 bg-rose-100 sm:aspect-[3/1]"><img src="{{ Storage::url($promoBanner->image_path) }}" alt="{{ $promoBanner->title }}" class="h-full w-full object-cover"><div class="absolute inset-0 bg-gradient-to-r from-stone-900/55 via-stone-900/10 to-transparent"></div><div class="absolute inset-0 flex items-end p-6 sm:p-9"><p class="max-w-lg font-serif text-3xl font-semibold text-white drop-shadow sm:text-4xl">{{ $promoBanner->title }}</p></div></a>
                @endforeach
                @if ($promoBanners->count() > 1)
                    <button type="button" @click="previous" class="absolute left-5 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-stone-700" aria-label="Banner sebelumnya">←</button><button type="button" @click="next" class="absolute right-5 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-stone-700" aria-label="Banner berikutnya">→</button><div class="absolute inset-x-0 bottom-4 flex justify-center gap-2"><template x-for="index in total"><button type="button" @click="active = index - 1" :class="active === index - 1 ? 'w-6 bg-white' : 'w-2 bg-white/60'" class="h-2 rounded-full transition-all" :aria-label="`Tampilkan banner ${index}`"></button></template></div>
                @endif
            </div>
        </section>
    @endif
    <section class="relative isolate overflow-hidden">
        <div class="absolute inset-0 -z-20 bg-[#fffaf5]"></div>
        <div class="absolute -left-40 top-24 -z-10 h-80 w-80 rounded-full bg-rose-100/70 blur-3xl"></div>
        <div class="absolute -right-20 bottom-0 -z-10 h-96 w-96 rounded-full bg-amber-100/60 blur-3xl"></div>
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
            <div class="max-w-xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-white px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-rose-600 shadow-sm"><span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>Rangkaian penuh makna</p>
                <h1 class="mt-6 font-serif text-5xl font-semibold leading-[1.05] tracking-tight text-stone-800 sm:text-6xl lg:text-7xl">Bunga untuk setiap <span class="italic text-rose-500">cerita</span> yang ingin disampaikan.</h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-stone-600 sm:text-lg">Dari “aku sayang kamu” hingga “aku turut berbahagia”, biarkan buket bunga pilihan kami menyampaikan rasa yang paling tulus.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-rose-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600">Pilih Buketmu <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10.22 3.22a.75.75 0 0 1 1.06 0l6.25 6.25a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 1 1-1.06-1.06l4.97-4.97H3.06a.75.75 0 0 1 0-1.5h12.13l-4.97-4.97a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg></a>
                    <a href="#koleksi" class="inline-flex items-center justify-center gap-2 rounded-full border border-stone-200 bg-white px-6 py-3.5 text-sm font-semibold text-stone-700 transition hover:border-rose-200 hover:text-rose-600">Lihat Koleksi</a>
                </div>
                <div class="mt-10 flex items-center gap-4 text-sm text-stone-500"><div class="flex -space-x-2"><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#fffaf5] bg-rose-200 text-[10px]">✿</span><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#fffaf5] bg-amber-200 text-[10px]">✿</span><span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#fffaf5] bg-pink-200 text-[10px]">✿</span></div><span><strong class="font-semibold text-stone-700">500+</strong> momen manis telah dirayakan</span></div>
            </div>
            <div class="relative mx-auto w-full max-w-lg lg:max-w-none">
                <div class="absolute -left-5 top-12 h-32 w-32 rounded-full border border-rose-200 sm:-left-10 sm:h-44 sm:w-44"></div>
                <div class="relative overflow-hidden rounded-t-[10rem] rounded-bl-[4rem] rounded-br-[4rem] bg-rose-100 p-3 shadow-2xl shadow-rose-200/50 sm:rounded-t-[12rem]"><img src="https://images.unsplash.com/photo-1563241527-3004b7be0ffd?auto=format&fit=crop&w=1100&q=85" alt="Buket bunga bernuansa merah muda" class="h-[440px] w-full rounded-t-[9.25rem] rounded-bl-[3.25rem] rounded-br-[3.25rem] object-cover sm:h-[560px] sm:rounded-t-[11.25rem]"></div>
                <div class="absolute -bottom-5 -left-4 rounded-2xl bg-white px-4 py-3 shadow-xl shadow-stone-300/40 sm:-left-8 sm:px-5"><div class="flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-full bg-rose-100 text-lg">♡</span><span class="text-xs leading-5 text-stone-500"><strong class="block text-sm text-stone-800">Dirangkai khusus</strong>untuk orang tersayang</span></div></div>
            </div>
        </div>
    </section>

    <section id="koleksi" class="scroll-mt-20 bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div class="max-w-xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Temukan yang tepat</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Buket untuk setiap momen.</h2></div><a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-rose-600 transition hover:text-rose-700">Lihat semua produk <span aria-hidden="true">→</span></a></div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['title' => 'Untuk Merayakan', 'image' => 'photo-1523438885200-e635ba2c371e'], ['title' => 'Untuk Mengungkapkan', 'image' => 'photo-1490750967868-88aa4486c946'], ['title' => 'Untuk Menguatkan', 'image' => 'photo-1561181286-d3fee7d55364'], ['title' => 'Untuk Hari Spesial', 'image' => 'photo-1582794543139-8ac9cb0e6aac']] as $collection)
                    <a href="{{ route('products.index') }}" class="group relative block h-72 overflow-hidden rounded-3xl bg-stone-100 sm:h-80"><img src="https://images.unsplash.com/{{ $collection['image'] }}?auto=format&fit=crop&w=700&q=80" alt="{{ $collection['title'] }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105"><div class="absolute inset-0 bg-gradient-to-t from-stone-900/65 via-stone-900/5 to-transparent"></div><span class="absolute bottom-5 left-5 flex items-center gap-2 text-lg font-semibold text-white">{{ $collection['title'] }} <span class="grid h-7 w-7 place-items-center rounded-full bg-white/20 text-sm backdrop-blur transition group-hover:bg-white group-hover:text-stone-800">→</span></span></a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="tentang" class="scroll-mt-20 bg-[#fdf5ef] py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:gap-20 lg:px-8">
            <div class="relative order-2 grid grid-cols-2 gap-4 lg:order-1"><img src="https://images.unsplash.com/photo-1487070183336-b863922373d4?auto=format&fit=crop&w=700&q=80" alt="Detail bunga segar" class="mt-10 h-64 w-full rounded-t-[5rem] rounded-bl-3xl object-cover sm:h-80"><img src="https://images.unsplash.com/photo-1526047932273-341f2a7631f9?auto=format&fit=crop&w=700&q=80" alt="Bunga yang dirangkai dengan hati-hati" class="h-64 w-full rounded-b-[5rem] rounded-tr-3xl object-cover sm:h-80"><div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-stone-800 px-4 py-2 text-center text-xs font-medium text-white shadow-lg">Made with love ✿</div></div>
            <div class="order-1 max-w-xl lg:order-2"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Dari hati, untuk hati</p><h2 class="mt-3 font-serif text-4xl font-semibold leading-tight tracking-tight text-stone-800 sm:text-5xl">Setiap tangkai punya cara sendiri untuk bicara.</h2><p class="mt-6 leading-7 text-stone-600">Kami percaya bunga bukan hanya hadiah. Ia adalah pengingat, pelukan, dan perayaan kecil yang bisa tinggal lebih lama di hati. Karena itu, setiap rangkaian Suki Craft dibuat satu per satu dengan perhatian pada setiap detailnya.</p>
                <div class="mt-8 grid gap-5 sm:grid-cols-2"><div class="rounded-2xl bg-white/70 p-5"><svg class="h-6 w-6 text-rose-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35 10.55 20C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.51L12 21.35Z" /></svg><h3 class="mt-3 font-semibold text-stone-800">Bunga pilihan</h3><p class="mt-1 text-sm leading-5 text-stone-500">Dipilih dalam kondisi terbaik untuk momen Anda.</p></div><div class="rounded-2xl bg-white/70 p-5"><svg class="h-6 w-6 text-rose-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3.75a4.5 4.5 0 0 0-4.5 4.5c0 5.25 4.5 12 4.5 12s4.5-6.75 4.5-12a4.5 4.5 0 0 0-4.5-4.5Zm0 6a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z" /></svg><h3 class="mt-3 font-semibold text-stone-800">Sentuhan personal</h3><p class="mt-1 text-sm leading-5 text-stone-500">Tambahkan pesan manis pada kartu ucapan Anda.</p></div></div>
            </div>
        </div>
    </section>

    <section id="cara-pesan" class="scroll-mt-20 bg-white py-20 sm:py-24"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mx-auto max-w-2xl text-center"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Semudah Itu Berbagi Bahagia</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Pilih, Pesan, dan Kirim dalam 3 Langkah.</h2></div><div class="relative mt-12 grid gap-8 md:grid-cols-3"><div class="absolute left-[16%] right-[16%] top-9 hidden border-t border-dashed border-rose-200 md:block"></div>@foreach ([['01', 'Pilih rangkaian', 'Temukan buket yang paling mewakili perasaan Anda.'], ['02', 'Tulis pesan', 'Tambahkan kartu ucapan untuk sentuhan yang lebih personal.'], ['03', 'Kami kirimkan', 'Pesanan Anda kami antar untuk menciptakan kejutan yang berkesan.']] as $step)<div class="relative text-center"><span class="mx-auto grid h-[72px] w-[72px] place-items-center rounded-full bg-rose-50 font-serif text-xl font-semibold text-rose-500 ring-8 ring-white">{{ $step[0] }}</span><h3 class="mt-5 text-lg font-semibold text-stone-800">{{ $step[1] }}</h3><p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-stone-500">{{ $step[2] }}</p></div>@endforeach</div></div></section>

    <section class="bg-rose-500 px-4 py-16 sm:px-6 sm:py-20 lg:px-8"><div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-8 text-center lg:flex-row lg:text-left"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-100">Ada pesan yang ingin disampaikan?</p><h2 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-white sm:text-5xl">Biarkan bunga yang bercerita.</h2></div><a href="{{ route('products.index') }}" class="shrink-0 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-rose-600 shadow-lg transition hover:-translate-y-0.5 hover:bg-rose-50">Belanja Sekarang</a></div></section>
@endsection
