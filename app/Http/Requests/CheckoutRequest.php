<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^(?:08[1-9][0-9]{7,11}|\\+628[1-9][0-9]{7,11})$/'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'delivery_date' => ['required', Rule::date()->format('Y-m-d')->afterOrEqual(today()->addDay())],
            'delivery_time_slot' => ['required', 'string', Rule::in($this->deliveryTimeSlots())],
            'notes' => ['nullable', 'string', 'max:1000'],
            'promotion_code' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_phone.regex' => 'Gunakan nomor telepon Indonesia dengan format 08xx atau +628xx.',
            'delivery_date.after_or_equal' => 'Tanggal pengiriman minimal H+1 dari hari ini.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_email' => $this->trimmedInput('customer_email'),
            'customer_name' => $this->trimmedInput('customer_name'),
            'customer_phone' => $this->trimmedInput('customer_phone'),
            'delivery_address' => $this->trimmedInput('delivery_address'),
            'delivery_time_slot' => $this->trimmedInput('delivery_time_slot'),
            'notes' => $this->trimmedInput('notes'),
            'promotion_code' => $this->trimmedInput('promotion_code'),
        ]);
    }

    private function deliveryTimeSlots(): array
    {
        return array_keys(config('delivery.time_slots', []));
    }

    private function trimmedInput(string $key): ?string
    {
        $value = $this->input($key);

        return filled($value) ? trim((string) $value) : null;
    }
}
