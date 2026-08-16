<?php

namespace App\Http\Requests\Customer\Auth;

use App\Http\Requests\Customer\Concerns\InteractsWithCustomerAccountRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CustomerRegistrationRequest extends FormRequest
{
    use InteractsWithCustomerAccountRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [...$this->accountRules(), 'password' => ['required', 'confirmed', Password::defaults()]];
    }

    public function messages(): array
    {
        return $this->accountMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? trim((string) $this->email) : null,
            'name' => filled($this->name) ? trim((string) $this->name) : null,
            'phone' => filled($this->phone) ? trim((string) $this->phone) : null,
        ]);
    }
}
