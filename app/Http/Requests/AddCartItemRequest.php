<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_message' => ['nullable', 'string', 'max:200'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'special_note' => ['nullable', 'string', 'max:300'],
            'variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ];
    }
}
