@props(['bankName', 'accountNumber', 'accountHolder'])

@php
    $normalizedBankName = Illuminate\Support\Str::of((string) $bankName)
        ->ascii()
        ->upper()
        ->replaceMatches('/[^A-Z0-9]+/', ' ')
        ->squish()
        ->toString();
    $isBca = in_array($normalizedBankName, ['BCA', 'BANK BCA', 'BANK CENTRAL ASIA', 'PT BANK CENTRAL ASIA TBK'], true);
    $displayBankName = $isBca ? 'Bank Central Asia' : ($bankName ?: __('storefront.bank_transfer.bank'));
    $bankCode = $isBca
        ? 'BCA'
        : Illuminate\Support\Str::of($normalizedBankName)
            ->replaceStart('BANK ', '')
            ->before(' ')
            ->limit(4, '')
            ->toString();
    $bankCode = $bankCode ?: 'BANK';
    $copyAccountNumber = Illuminate\Support\Str::of((string) $accountNumber)
        ->replaceMatches('/\s+/', '')
        ->toString();
    $displayAccountNumber = preg_match('/^\d+$/', $copyAccountNumber) === 1
        ? Illuminate\Support\Str::of($copyAccountNumber)->replaceMatches('/(\d{4})(?=\d)/', '$1 ')->toString()
        : ($accountNumber ?: __('storefront.bank_transfer.account_unset'));
@endphp

<div {{ $attributes->class('min-w-0 rounded-2xl border border-rose-100 bg-white p-5 shadow-sm') }} data-bank-transfer-card>
    <p class="text-xs font-bold uppercase tracking-[0.16em] text-rose-500">{{ __('storefront.bank_transfer.manual') }}</p>

    <div class="mt-4 flex min-w-0 items-center gap-3">
        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-500 text-xs font-bold tracking-wide text-white shadow-sm" data-bank-code="{{ $bankCode }}">
            {{ $bankCode }}
        </span>
        <p class="min-w-0 whitespace-nowrap font-serif text-base font-semibold leading-tight text-stone-800 sm:text-lg" data-bank-name>{{ $displayBankName }}</p>
    </div>

    <div class="mt-5 border-t border-stone-200 pt-5">
        <p class="text-xs font-medium text-stone-500">{{ __('storefront.bank_transfer.account_number') }}</p>
        <div class="mt-2 flex flex-nowrap items-center justify-between gap-3">
            <p class="whitespace-nowrap font-mono text-lg font-bold tracking-wide text-stone-900 sm:text-xl">{{ $displayAccountNumber }}</p>
            <button
                type="button"
                class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-full border border-rose-200 bg-white px-3 text-xs font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                data-copy-account
                data-copy-value="{{ $copyAccountNumber }}"
                data-copy-label="{{ __('storefront.bank_transfer.copy') }}"
                data-copied-label="{{ __('storefront.bank_transfer.copied') }}"
                @disabled(blank($copyAccountNumber))
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="8" y="8" width="11" height="11" rx="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/>
                </svg>
                <span data-copy-account-label>{{ __('storefront.bank_transfer.copy') }}</span>
            </button>
        </div>
    </div>

    @if (filled($accountHolder))
        <div class="mt-5 border-t border-stone-100 pt-4">
            <p class="text-xs font-medium text-stone-500">{{ __('storefront.bank_transfer.account_holder') }}</p>
            <p class="mt-1 text-sm font-semibold text-stone-800">{{ $accountHolder }}</p>
        </div>
    @endif

    <p class="mt-5 border-t border-stone-100 pt-4 text-xs leading-5 text-stone-500">{{ __('storefront.bank_transfer.hint') }}</p>
</div>
