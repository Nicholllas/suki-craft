<?php

namespace App\Http\Requests\Customer\Auth;

use App\Services\PhoneNumberNormalizer;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['login' => ['required', 'string', 'max:255'], 'password' => ['required', 'string']];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::guard('customer')->attempt($this->credentials(), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages(['login' => 'Email atau nomor WhatsApp dan password tidak sesuai.']);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));
        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    protected function prepareForValidation(): void
    {
        $login = filled($this->login) ? trim((string) $this->login) : null;

        $this->merge([
            'login' => filled($login) && ! filter_var($login, FILTER_VALIDATE_EMAIL) && PhoneNumberNormalizer::isValid($login)
                ? PhoneNumberNormalizer::normalize($login)
                : $login,
        ]);
    }

    private function credentials(): array
    {
        return filter_var($this->login, FILTER_VALIDATE_EMAIL)
            ? ['email' => $this->login, 'password' => $this->password]
            : ['phone' => PhoneNumberNormalizer::equivalentNumbers($this->login), 'password' => $this->password];
    }

    private function throttleKey(): string
    {
        $login = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? Str::lower($this->string('login')) : PhoneNumberNormalizer::normalize($this->login);

        return 'customer|'.Str::transliterate($login.'|'.$this->ip());
    }
}
