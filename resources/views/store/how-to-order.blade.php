@extends('layouts.store')

@section('title', 'Cara Pesan | Suki Craft')
@section('description', 'Panduan mudah memesan buket Suki Craft, dari memilih rangkaian hingga melacak pengiriman.')

@section('content')
    <section class="relative isolate overflow-hidden bg-[#fff8f3]">
        <div class="absolute -left-20 top-8 -z-10 h-64 w-64 rounded-full bg-rose-200/60 blur-3xl"></div>
        <div class="absolute -right-16 bottom-0 -z-10 h-80 w-80 rounded-full bg-amber-100/70 blur-3xl"></div>

        <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_.95fr] lg:gap-20 lg:px-8 lg:py-28">
            <div class="max-w-2xl">
                <p class="inline-flex items-center gap-2 rounded-full border border-rose-100 bg-white px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-rose-600 shadow-sm"><span class="h-1.5 w-1.5 rounded-full bg-rose-400"></span>Belanja tanpa bingung</p>
                <h1 class="mt-6 font-serif text-5xl font-semibold leading-[1.05] tracking-tight text-stone-800 sm:text-6xl">Dari pilih buket sampai tiba di tangan penerima.</h1>
                <p class="mt-6 max-w-xl text-base leading-7 text-stone-600 sm:text-lg">Ikuti enam langkah sederhana ini untuk membuat kejutan yang terasa personal. Anda dapat mulai memilih buket tanpa perlu login.</p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-rose-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-rose-200 transition hover:-translate-y-0.5 hover:bg-rose-600">Mulai pilih buket <span aria-hidden="true">→</span></a>
                    <a href="{{ route('tracking.create') }}" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-white px-6 py-3.5 text-sm font-semibold text-stone-700 transition hover:border-rose-200 hover:text-rose-600">Lacak pesanan</a>
                </div>
            </div>

            <div class="rounded-[2.5rem] border border-rose-100 bg-white/90 p-5 shadow-xl shadow-rose-900/5 backdrop-blur sm:p-7">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Ringkasan perjalanan pesanan</p>
                <ol class="mt-6 space-y-4">
                    @foreach ([['number' => '01', 'label' => 'Pilih buket'], ['number' => '02', 'label' => 'Tambahkan sentuhan personal'], ['number' => '03', 'label' => 'Periksa keranjang'], ['number' => '04', 'label' => 'Atur pengiriman'], ['number' => '05', 'label' => 'Bayar dan unggah bukti'], ['number' => '06', 'label' => 'Lacak hingga sampai']] as $step)
                        <li class="flex items-center gap-4 rounded-2xl bg-[#fffaf7] px-4 py-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-rose-100 text-xs font-bold text-rose-600">{{ $step['number'] }}</span><span class="text-sm font-semibold text-stone-700">{{ $step['label'] }}</span></li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Enam langkah mudah</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Satu per satu, sampai kejutan Anda terkirim.</h2><p class="mt-5 text-sm leading-6 text-stone-500 sm:text-base">Setiap tahap dirancang agar Anda tetap tahu apa yang perlu dilakukan berikutnya.</p></div>

            <div class="relative mt-12 grid gap-5 lg:grid-cols-2">
                <div class="absolute bottom-12 left-1/2 top-12 hidden border-l border-dashed border-rose-200 lg:block"></div>
                @foreach ([['number' => '01', 'title' => 'Temukan buket yang tepat', 'description' => 'Buka katalog, lalu pilih rangkaian yang paling sesuai dengan momen dan perasaan yang ingin Anda sampaikan.', 'action' => 'Jelajahi koleksi', 'route' => 'products.index', 'icon' => '✿'], ['number' => '02', 'title' => 'Buat buket lebih personal', 'description' => 'Pilih varian yang tersedia, tulis pesan kartu ucapan, tambahkan catatan untuk florist, lalu tentukan jumlah buket.', 'action' => null, 'route' => null, 'icon' => '♡'], ['number' => '03', 'title' => 'Simpan ke keranjang', 'description' => 'Tekan tombol tambah ke keranjang. Di sana, Anda dapat meninjau kembali detail buket, jumlah, dan subtotal pesanan.', 'action' => 'Buka keranjang', 'route' => 'cart.index', 'icon' => '⌑'], ['number' => '04', 'title' => 'Isi detail pengiriman', 'description' => 'Lanjutkan ke checkout untuk memasukkan nama penerima, WhatsApp, alamat lengkap, tanggal, serta slot waktu pengiriman.', 'action' => 'Lihat katalog', 'route' => 'products.index', 'icon' => '⌖'], ['number' => '05', 'title' => 'Buat pesanan dan bayar', 'description' => 'Setelah pesanan dibuat, lakukan pembayaran melalui QRIS atau transfer bank, kemudian unggah bukti pembayaran untuk diverifikasi.', 'action' => null, 'route' => null, 'icon' => '✓'], ['number' => '06', 'title' => 'Pantau perjalanan pesanan', 'description' => 'Gunakan menu Lacak Pesanan untuk melihat pembaruan status, dari verifikasi pembayaran hingga buket diterima.', 'action' => 'Lacak pesanan', 'route' => 'tracking.create', 'icon' => '⌁']] as $step)
                    <article class="relative rounded-3xl border border-stone-100 bg-[#fffaf7] p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg hover:shadow-rose-900/5 sm:p-7">
                        <div class="flex items-start justify-between gap-4"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-rose-100 text-xl text-rose-600">{{ $step['icon'] }}</span><span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-rose-500 ring-1 ring-rose-100">{{ $step['number'] }}</span></div>
                        <h3 class="mt-6 font-serif text-2xl font-semibold text-stone-800">{{ $step['title'] }}</h3>
                        <p class="mt-3 text-sm leading-6 text-stone-500">{{ $step['description'] }}</p>
                        @if ($step['route'])
                            <a href="{{ route($step['route']) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-rose-600 transition hover:text-rose-700">{{ $step['action'] }} <span aria-hidden="true">→</span></a>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-[#fdf5ef] py-20 sm:py-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-[.9fr_1.1fr] lg:items-start lg:gap-20 lg:px-8">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Sebelum checkout</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Siapkan detail kecil agar pengiriman terasa tepat.</h2><p class="mt-5 max-w-xl leading-7 text-stone-600">Semakin lengkap informasi yang Anda berikan, semakin mudah tim kami menyiapkan rangkaian dan kurir mengantarkannya.</p></div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([['title' => 'Alamat lengkap', 'description' => 'Sertakan nomor rumah, area, dan patokan yang memudahkan kurir.'], ['title' => 'Nomor WhatsApp aktif', 'description' => 'Gunakan nomor yang dapat dihubungi jika kurir membutuhkan konfirmasi.'], ['title' => 'Jadwal pengiriman', 'description' => 'Pilih tanggal serta slot waktu yang sesuai dengan rencana penerima.'], ['title' => 'Pesan kartu & catatan', 'description' => 'Cek kembali ejaan pesan dan tuliskan kebutuhan khusus bila ada.']] as $detail)
                    <div class="rounded-3xl bg-white p-6 shadow-sm"><span class="text-xl text-rose-500">✦</span><h3 class="mt-4 text-lg font-semibold text-stone-800">{{ $detail['title'] }}</h3><p class="mt-2 text-sm leading-6 text-stone-500">{{ $detail['description'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-white py-20 sm:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="text-center"><p class="text-xs font-bold uppercase tracking-[0.2em] text-rose-500">Pertanyaan umum</p><h2 class="mt-3 font-serif text-4xl font-semibold tracking-tight text-stone-800 sm:text-5xl">Yang perlu Anda tahu sebelum memesan.</h2></div>
            <div class="mt-10 divide-y divide-stone-100 rounded-3xl border border-stone-100 bg-[#fffaf7] px-5 sm:px-7">
                @foreach ([['question' => 'Apakah saya perlu membuat akun untuk memesan?', 'answer' => 'Tidak. Anda dapat memilih buket, mengisi detail penerima, dan membuat pesanan sebagai tamu. Akun pelanggan dapat digunakan bila Anda ingin mengelola profil dan riwayat pesanan.'], ['question' => 'Kapan saya membayar pesanan?', 'answer' => 'Pembayaran dilakukan setelah pesanan dibuat. Halaman konfirmasi akan menampilkan QRIS atau informasi transfer bank, lalu Anda dapat mengunggah bukti pembayaran.'], ['question' => 'Bagaimana cara mengetahui status pesanan?', 'answer' => 'Buka menu Lacak Pesanan untuk melihat perkembangan pesanan setelah pembayaran dikonfirmasi dan pengiriman dijadwalkan.']] as $faq)
                    <details class="group py-5"><summary class="flex cursor-pointer list-none items-center justify-between gap-5 text-left font-semibold text-stone-800"><span>{{ $faq['question'] }}</span><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-rose-100 text-lg text-rose-600 transition group-open:rotate-45">+</span></summary><p class="max-w-3xl pt-4 text-sm leading-6 text-stone-500">{{ $faq['answer'] }}</p></details>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-rose-500 px-4 py-16 sm:px-6 sm:py-20 lg:px-8">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-8 text-center lg:flex-row lg:text-left"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-100">Siap membuat kejutan?</p><h2 class="mt-2 font-serif text-4xl font-semibold tracking-tight text-white sm:text-5xl">Pilih buket yang paling mewakili cerita Anda.</h2></div><a href="{{ route('products.index') }}" class="shrink-0 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-rose-600 shadow-lg transition hover:-translate-y-0.5 hover:bg-rose-50">Lihat koleksi buket</a></div>
    </section>
@endsection
