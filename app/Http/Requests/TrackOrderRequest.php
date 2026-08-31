<?php

namespace App\Http\Requests;

use App\Services\PhoneNumberNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class TrackOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:25', PhoneNumberNormalizer::validationRule()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'order_number' => trim((string) $this->input('order_number')),
            'phone' => trim((string) $this->input('phone')),
        ]);
    }

    public function messages(): array
    {
        return ['phone.regex' => 'Gunakan nomor telepon Indonesia dengan format 08xx, +628xx, atau 628xx.'];
    }
}
