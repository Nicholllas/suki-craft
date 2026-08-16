<div>
    <!-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin -->
</div>
@props(['action', 'orderItem'])

<div x-data="{ open: false, rating: {{ (int) old('rating', 0) }} }">
    <button type="button" @click="open = true" class="inline-flex items-center justify-center rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600">Beri Ulasan</button>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-end bg-stone-900/50 p-4 sm:items-center sm:justify-center" @keydown.escape.window="open = false">
        <div @click.outside="open = false" class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-2xl sm:p-7">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Ulasan produk</p><h3 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Bagikan pengalamanmu</h3><p class="mt-2 text-sm leading-6 text-stone-500">{{ $orderItem->product_name }}</p></div>
                <button type="button" @click="open = false" class="grid h-9 w-9 place-items-center rounded-xl text-stone-400 transition hover:bg-stone-100 hover:text-stone-700" aria-label="Tutup form ulasan">×</button>
            </div>

            <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <fieldset>
                    <legend class="text-sm font-semibold text-stone-800">Rating</legend>
                    <div class="mt-2 flex gap-1" role="radiogroup" aria-label="Rating produk">
                        @foreach(range(1, 5) as $star)
                            <button type="button" @click="rating = {{ $star }}" :aria-checked="rating === {{ $star }}" :class="rating >= {{ $star }} ? 'text-amber-400' : 'text-stone-300'" class="text-3xl leading-none transition hover:scale-110" role="radio">★<span class="sr-only">{{ $star }} bintang</span></button>
                        @endforeach
                    </div>
                    <input type="hidden" name="rating" :value="rating">
                    @error('rating')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </fieldset>

                <div>
                    <label for="review-comment-{{ $orderItem->id }}" class="text-sm font-semibold text-stone-800">Komentar <span class="font-normal text-stone-400">(opsional)</span></label>
                    <textarea id="review-comment-{{ $orderItem->id }}" name="comment" rows="4" maxlength="1000" placeholder="Ceritakan kesegaran bunga dan pengalamanmu." class="mt-2 w-full rounded-xl border-stone-200 px-4 py-3 text-sm leading-6 text-stone-700 placeholder:text-stone-400 focus:border-rose-300 focus:ring-rose-200">{{ old('comment') }}</textarea>
                    @error('comment')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="review-photo-{{ $orderItem->id }}" class="text-sm font-semibold text-stone-800">Foto buket <span class="font-normal text-stone-400">(opsional)</span></label>
                    <input id="review-photo-{{ $orderItem->id }}" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-stone-200 px-3 py-2 text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-rose-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-rose-700 hover:file:bg-rose-100">
                    <p class="mt-2 text-xs text-stone-400">JPG, PNG, atau WEBP hingga 5 MB.</p>
                    @error('photo')<p class="mt-2 text-xs font-medium text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" @click="open = false" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-500 transition hover:bg-stone-100">Batal</button>
                    <button type="submit" :disabled="rating === 0" class="rounded-xl bg-rose-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-600 disabled:cursor-not-allowed disabled:opacity-50">Kirim ulasan</button>
                </div>
            </form>
        </div>
    </div>
</div>
