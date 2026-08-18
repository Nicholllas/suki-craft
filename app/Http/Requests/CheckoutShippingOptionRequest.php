<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutShippingOptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'courier_company' => ['required', 'string', 'max:50'],
            'courier_service' => ['required', 'string', 'max:100'],
        ];
    }
}
