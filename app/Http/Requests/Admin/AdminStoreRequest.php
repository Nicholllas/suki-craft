<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\InteractsWithAdminAccountRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AdminStoreRequest extends FormRequest
{
    use InteractsWithAdminAccountRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->accountRules(),
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

}
