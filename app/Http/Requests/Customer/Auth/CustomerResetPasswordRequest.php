<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CustomerResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'token' => ['required'],
        ];
    }
}
