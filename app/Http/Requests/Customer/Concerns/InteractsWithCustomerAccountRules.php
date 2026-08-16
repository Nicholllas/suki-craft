<?php

namespace App\Http\Requests\Customer\Concerns;

use App\Models\Customer;
use Illuminate\Validation\Rule;

trait InteractsWithCustomerAccountRules
{
    protected function accountRules(?Customer $customer = null): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('customers')->ignore($customer?->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25', 'regex:/^(?:08[1-9][0-9]{7,11}|\\+628[1-9][0-9]{7,11})$/', Rule::unique('customers')->ignore($customer?->id)],
        ];
    }

    protected function accountMessages(): array
    {
        return ['phone.regex' => 'Gunakan nomor WhatsApp Indonesia dengan format 08xx atau +628xx.'];
    }
}
