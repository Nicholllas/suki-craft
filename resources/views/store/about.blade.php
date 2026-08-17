@extends('layouts.store')

@section('title', 'Tentang Suki Craft | Cerita di Balik Setiap Rangkaian')
@section('description', 'Kenali cerita, nilai, tim, dan cara Suki Craft merangkai bunga untuk setiap momen istimewa.')

@section('content')
    <section class="relative isolate overflow-hidden bg-[#fff8f3]">
        <div class="absolute -left-24 top-10 -z-10 h-72 w-72 rounded-full bg-rose-200/50 blur-3xl"></div>
        <div class="absolute -right-24 bottom-0 -z-10 h-80 w-80 rounded-full bg-amber-100/70 blur-3xl"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_.95fr] lg:gap-20 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-white px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-rose-600 shadow-sm"><span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>Di balik setiap tangkai</p>
                <h1 class="mt-6 font-serif text-5xl font-semibold leading-[1.05] tracking-tight text-stone-800 sm:text-6xl">Kami merangkai <span class="italic text-rose-500">perasaan</span>, bukan sekadar bunga.</h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-stone-600 sm:text-lg">Suki Craft adalah ruang kecil untuk membantu setiap orang menyampaikan cerita, rasa sayang, dan perhatian melalui rangkaian yang dibuat dengan penuh ketelitian.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-rose-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600">Lihat Koleksi Buket <span aria-hidden="true">→</span></a>
                    <a href="#kontak" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-white px-6 py-3.5 text-sm font-semibold text-stone-700 transition hover:border-rose-200 hover:text-rose-600">Hubungi Kami</a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-lg">
                <div class="absolute -left-5 top-12 h-36 w-36 rounded-full border border-rose-200 sm:-left-10 sm:h-48 sm:w-48"></div>
                <div class="relative overflow-hidden rounded-t-[9rem] rounded-bl-[4rem] rounded-br-[4rem] bg-rose-100 p-3 shadow-2xl shadow-rose-200/50 sm:rounded-t-[11rem]">
                    <div class="flex h-[410px] flex-col justify-between rounded-t-[8.25rem] rounded-bl-[3.25rem] rounded-br-[3.25rem] bg-gradient-to-b from-[#eeb6a9] via-[#f8d7ce] to-[#fcebe3] p-8 sm:h-[500px] sm:rounded-t-[10.25rem] sm:p-12">
                        <p class="max-w-[12rem] font-serif text-3xl font-semibold leading-tight text-white drop-shadow-sm sm:text-4xl">Dari hati, untuk hati.</p>
                        <div class="relative mx-auto h-48 w-56 sm:h-56 sm:w-64" aria-hidden="true">
                            <span class="absolute left-3 top-5 h-24 w-24 rounded-full bg-rose-200/95 shadow-lg shadow-rose-900/10"></span>
                            <span class="absolute right-1 top-1 h-28 w-28 rounded-full bg-[#f7d99b] shadow-lg shadow-amber-900/10"></span>
                            <span class="absolute bottom-1 left-16 h-28 w-28 rounded-full bg-[#e6a1b0] shadow-lg shadow-rose-900/10"></span>
                            <span class="absolute bottom-0 left-1/2 h-28 w-5 -translate-x-1/2 rotate-[18deg] rounded-full bg-emerald-700/70"></span>
                            <span class="absolute bottom-0 left-[44%] h-24 w-5 -translate-x-1/2 -rotate-[20deg] rounded-full bg-emerald-700/60"></span>
                            <span class="absolute bottom-0 left-[58%] h-24 w-5 -translate-x-1/2 rotate-[38deg] rounded-full bg-emerald-700/60"></span>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-5 -left-3 rounded-2xl bg-white px-4 py-3 shadow-xl shadow-stone-300/40 sm:-left-7 sm:px-5"><p class="text-xs text-stone-500">Dirangkai dengan sepenuh hati</p><p class="mt-0.5 font-serif text-lg font-semibold text-stone-800">Suki Craft ✿</p></div>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[.85fr_1.15fr] lg:items-start lg:gap-20 lg:px-8">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">01 · Apa yang kami lakukan</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Buket yang terasa personal sejak pertama dilihat.</h2></div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([['title' => 'Rangkaian harian', 'description' => 'Pilihan buket segar untuk hadiah kecil, kejutan spontan, dan momen yang ingin diperingati.'], ['title' => 'Pesanan personal', 'description' => 'Rangkaian yang bisa disesuaikan dengan warna, pesan kartu, hingga cerita di balik pemberiannya.'], ['title' => 'Momen spesial', 'description' => 'Buket untuk ulang tahun, wisuda, perayaan, dan hari-hari yang layak diberi perhatian lebih.'], ['title' => 'Pengiriman berkesan', 'description' => 'Kami menyiapkan setiap pesanan agar tiba rapi, hangat, dan siap membuat penerimanya tersenyum.']] as $service)
                    <article class="rounded-3xl border border-stone-100 bg-[#fffaf7] p-6 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-900/5"><span class="grid h-10 w-10 place-items-center rounded-2xl bg-rose-100 text-lg text-rose-600">✿</span><h3 class="mt-5 text-lg font-semibold text-stone-800">{{ $service['title'] }}</h3><p class="mt-2 text-sm leading-6 text-stone-500">{{ $service['description'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#fdf5ef] py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">02 · Sejarah kami</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Tumbuh dari keinginan sederhana untuk membuat hari seseorang lebih indah.</h2></div>
            <ol class="relative mt-12 grid gap-8 md:grid-cols-3">
                @foreach ([['year' => '2021', 'title' => 'Berawal dari meja kecil', 'description' => 'Suki Craft dimulai dari pesanan buket untuk orang-orang terdekat.'], ['year' => '2023', 'title' => 'Menjadi bagian banyak cerita', 'description' => 'Kami mulai membantu lebih banyak momen, dari kejutan kecil hingga perayaan besar.'], ['year' => 'Hari ini', 'title' => 'Terus merangkai dengan hangat', 'description' => 'Kami terus belajar, mendengar, dan menciptakan pengalaman kirim bunga yang lebih personal.']] as $milestone)
                    <li class="relative rounded-3xl bg-white p-6 shadow-sm"><span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">{{ $milestone['year'] }}</span><h3 class="mt-5 text-xl font-semibold text-stone-800">{{ $milestone['title'] }}</h3><p class="mt-2 text-sm leading-6 text-stone-500">{{ $milestone['description'] }}</p></li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end"><div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">03 · Portofolio</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Cerita yang pernah kami bantu sampaikan.</h2></div><p class="max-w-sm text-sm leading-6 text-stone-500">Contoh portofolio ini dapat diganti dengan dokumentasi proyek, acara, atau rangkaian unggulan Suki Craft.</p></div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['title' => 'Birthday Surprise', 'tone' => 'from-rose-200 via-rose-100 to-amber-50'], ['title' => 'Graduation Day', 'tone' => 'from-amber-200 via-amber-100 to-stone-50'], ['title' => 'Intimate Wedding', 'tone' => 'from-pink-200 via-pink-100 to-rose-50'], ['title' => 'Corporate Gifting', 'tone' => 'from-violet-200 via-violet-100 to-rose-50']] as $portfolio)
                    <article class="group overflow-hidden rounded-3xl border border-stone-100 bg-white shadow-sm"><div class="flex aspect-[4/5] items-end bg-gradient-to-br {{ $portfolio['tone'] }} p-5"><span class="grid h-16 w-16 place-items-center rounded-full border border-white/70 bg-white/40 text-3xl shadow-sm backdrop-blur">✿</span></div><div class="p-5"><p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-500">Contoh proyek</p><h3 class="mt-2 text-lg font-semibold text-stone-800">{{ $portfolio['title'] }}</h3></div></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-stone-900 py-20 text-white sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-300">04 · Tim kami</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight sm:text-5xl">Orang-orang yang menaruh perhatian pada detail kecil.</h2></div>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['name' => 'Naya Pradita', 'role' => 'Founder & Creative Lead', 'initial' => 'NP'], ['name' => 'Alya Ramadhani', 'role' => 'Floral Designer', 'initial' => 'AR'], ['name' => 'Dimas Putra', 'role' => 'Customer Experience', 'initial' => 'DP'], ['name' => 'Rara Maheswari', 'role' => 'Delivery Coordinator', 'initial' => 'RM']] as $member)
                    <article class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10"><div class="grid h-14 w-14 place-items-center rounded-2xl bg-rose-300 font-serif text-lg font-semibold text-stone-900">{{ $member['initial'] }}</div><h3 class="mt-5 text-lg font-semibold">{{ $member['name'] }}</h3><p class="mt-1 text-sm text-stone-400">{{ $member['role'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#fff8f3] py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-[.8fr_1.2fr] lg:gap-20 lg:px-8">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">05 · Prinsip dan nilai</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Cara kami bekerja selalu dimulai dari rasa peduli.</h2></div>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([['title' => 'Penuh perhatian', 'description' => 'Kami mendengar kebutuhan setiap pelanggan sebelum merangkai.'], ['title' => 'Jujur dalam kualitas', 'description' => 'Kami menjaga kualitas bunga dan memberi informasi dengan apa adanya.'], ['title' => 'Bertumbuh bersama', 'description' => 'Kami terus belajar bersama tim, mitra, dan pelanggan.']] as $value)
                    <article class="rounded-3xl bg-white p-6 shadow-sm"><span class="text-2xl text-rose-500">♡</span><h3 class="mt-5 text-lg font-semibold text-stone-800">{{ $value['title'] }}</h3><p class="mt-2 text-sm leading-6 text-stone-500">{{ $value['description'] }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">06 · Penghargaan</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Hal kecil yang membuat kami terus melangkah.</h2><p class="mt-4 text-sm leading-6 text-stone-500">Bagian ini adalah placeholder untuk penghargaan, sertifikasi, atau pencapaian Suki Craft di masa mendatang.</p></div>
            <div class="mt-10 grid gap-4 md:grid-cols-3">
                @foreach (['Pilihan Pelanggan 2024', 'Kreasi Lokal Favorit', 'Komitmen Pelayanan'] as $award)
                    <div class="flex items-center gap-4 rounded-3xl border border-amber-100 bg-amber-50/60 p-6"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-200 text-xl text-amber-800">★</span><p class="font-semibold text-stone-800">{{ $award }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#fdf5ef] py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">07 · Ulasan</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Kata hangat dari mereka yang pernah berbagi cerita.</h2></div>
            <div class="mt-10 grid gap-5 lg:grid-cols-3">
                @foreach ([['quote' => 'Buketnya membuat momen ulang tahun sahabat saya terasa jauh lebih spesial.', 'name' => 'Dina A.', 'moment' => 'Pesanan ulang tahun'], ['quote' => 'Proses pesannya mudah, dan kartu ucapannya ditulis dengan sangat rapi.', 'name' => 'Rafi P.', 'moment' => 'Hadiah apresiasi'], ['quote' => 'Warnanya sesuai permintaan dan rangkaiannya terlihat indah saat tiba.', 'name' => 'Maya S.', 'moment' => 'Kejutan wisuda']] as $review)
                    <figure class="rounded-3xl bg-white p-7 shadow-sm"><div class="text-lg tracking-[0.2em] text-amber-400">★★★★★</div><blockquote class="mt-5 font-serif text-xl leading-8 text-stone-800">“{{ $review['quote'] }}”</blockquote><figcaption class="mt-6 border-t border-stone-100 pt-5"><p class="font-semibold text-stone-800">{{ $review['name'] }}</p><p class="mt-1 text-sm text-stone-500">{{ $review['moment'] }}</p></figcaption></figure>
                @endforeach
            </div>
        </div>
    </section>

    <section id="kontak" class="bg-white py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[.85fr_1.15fr] lg:items-center lg:gap-20 lg:px-8">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">08 · Kontak</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Ada cerita yang ingin Anda sampaikan?</h2><p class="mt-5 max-w-xl leading-7 text-stone-600">Tim kami siap membantu menemukan rangkaian yang paling tepat untuk momen Anda. Detail kontak di bawah masih bersifat contoh dan dapat disesuaikan.</p></div>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([['label' => 'WhatsApp', 'value' => '+62 812 0000 0000'], ['label' => 'Email', 'value' => 'hello@sukicraft.com'], ['label' => 'Lokasi', 'value' => 'Indonesia']] as $contact)
                    <div class="rounded-3xl border border-stone-100 bg-[#fffaf7] p-6"><p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">{{ $contact['label'] }}</p><p class="mt-3 break-words font-semibold text-stone-800">{{ $contact['value'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-rose-500 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-8 text-center lg:flex-row lg:text-left"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-100">09 · Mari mulai ceritanya</p><h2 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-white sm:text-5xl">Temukan buket untuk perasaan yang ingin Anda sampaikan.</h2></div><a href="{{ route('products.index') }}" class="shrink-0 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-rose-600 shadow-lg transition hover:-translate-y-0.5 hover:bg-rose-50">Belanja Buket</a></div>
    </section>
@endsection
