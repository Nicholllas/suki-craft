@props(['deliveryProofUrl' => null, 'order'])

<section class="rounded-3xl border border-stone-200 bg-white p-5 sm:p-7">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">Perjalanan pesanan</p>
            <h2 class="mt-2 font-serif text-2xl font-semibold text-stone-800">Lacak buketmu</h2>
            <p class="mt-2 text-sm leading-6 text-stone-500">Kami memperbarui setiap tahap agar kamu tahu pesanan sedang berada di mana.</p>
        </div>
        <x-status-badge :status="$order->status->label()" />
    </div>

    @if ($order->courier && in_array($order->status, [\App\Enums\OrderStatus::OUT_FOR_DELIVERY, \App\Enums\OrderStatus::DELIVERED], true))
        <div class="mt-5 rounded-2xl bg-sky-50 px-4 py-3 text-sm text-sky-800">
            <p class="font-semibold">Kurir pengantaran: {{ $order->courier->name }}</p>
            <p class="mt-1 text-sky-700">{{ $order->courier->phone }}</p>
        </div>
    @endif

    @if ($order->status === \App\Enums\OrderStatus::CANCELLED && $order->cancellation_reason)
        <div class="mt-5 rounded-2xl bg-rose-50 px-4 py-4 text-sm leading-6 text-rose-800">
            <p class="font-semibold">Pesanan dibatalkan</p>
            <p class="mt-1 text-rose-700">{{ $order->cancellation_reason }}</p>
        </div>
    @endif

    <ol class="mt-7 space-y-0">
        @forelse ($order->statusHistories as $history)
            <li class="relative flex gap-4 pb-6 last:pb-0">
                @if (! $loop->last)
                    <span class="absolute left-[0.8125rem] top-8 h-[calc(100%-1.5rem)] w-px bg-stone-200"></span>
                @endif
                <span class="relative z-10 grid h-7 w-7 shrink-0 place-items-center rounded-full {{ $loop->last ? 'bg-rose-500 text-white ring-4 ring-rose-100' : 'bg-emerald-500 text-white' }}">
                    @if ($loop->last)
                        <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                    @else
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                    @endif
                </span>
                <div class="min-w-0 pb-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <p class="font-semibold text-stone-800">{{ $history->status->label() }}</p>
                        @if ($loop->last)
                            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-600">Status saat ini</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-stone-400">{{ $history->created_at->locale('id')->translatedFormat('d F Y, H.i') }} WIB</p>
                    @if ($history->note)
                        <p class="mt-2 text-sm leading-6 text-stone-500">{{ $history->note }}</p>
                    @endif
                </div>
            </li>
        @empty
            <li class="rounded-2xl bg-stone-50 px-4 py-4 text-sm text-stone-500">Belum ada pembaruan status untuk pesanan ini.</li>
        @endforelse
    </ol>

    @if ($order->status === \App\Enums\OrderStatus::DELIVERED && $deliveryProofUrl)
        <div class="mt-7 border-t border-stone-100 pt-6">
            <p class="text-sm font-semibold text-stone-800">Bukti pengiriman</p>
            <a href="{{ $deliveryProofUrl }}" target="_blank" rel="noopener noreferrer" class="mt-3 block overflow-hidden rounded-2xl border border-stone-200 bg-stone-50">
                <img src="{{ $deliveryProofUrl }}" alt="Bukti pengiriman pesanan {{ $order->order_number }}" class="max-h-96 w-full object-contain">
            </a>
        </div>
    @endif
</section>
