<?php

namespace App\Http\Requests\Customer\Concerns;

use App\Models\Customer;
use App\Services\PhoneNumberNormalizer;
use Illuminate\Validation\Rule;

trait InteractsWithCustomerAccountRules
{
    protected function accountRules(?Customer $customer = null): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->ignore($customer?->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', PhoneNumberNormalizer::validationRule(), Rule::unique('customers')->ignore($customer?->id)],
        ];
    }

    protected function accountMessages(): array
    {
        return ['phone.regex' => __('store.validation.messages.whatsapp_format')];
    }

    protected function normalizedPhoneInput(): ?string
    {
        $phone = filled($this->phone) ? trim((string) $this->phone) : null;

        return filled($phone) && PhoneNumberNormalizer::isValid($phone) ? PhoneNumberNormalizer::normalize($phone) : $phone;
    }
}
