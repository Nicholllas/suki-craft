<?php

namespace App\Http\Requests\Customer\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CustomerForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['email' => ['required', 'email']];
    }
}
