<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\Customer\Concerns\InteractsWithCustomerAccountRules;
use Illuminate\Foundation\Http\FormRequest;

class CustomerProfileUpdateRequest extends FormRequest
{
    use InteractsWithCustomerAccountRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...$this->accountRules($this->user('customer')),
            'address' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return $this->accountMessages();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => filled($this->email) ? trim((string) $this->email) : null,
            'address' => filled($this->address) ? trim((string) $this->address) : null,
            'name' => filled($this->name) ? trim((string) $this->name) : null,
            'phone' => $this->normalizedPhoneInput(),
        ]);
    }
}
