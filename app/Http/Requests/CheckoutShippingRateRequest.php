<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination_area_id' => ['required', 'string', 'max:100'],
            'destination_postal_code' => ['nullable', 'integer', 'between:10000,99999'],
        ];
    }
}
