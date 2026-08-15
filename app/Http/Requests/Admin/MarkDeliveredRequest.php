<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class MarkDeliveredRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'proof_photo' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max('5mb')],
        ];
    }
}
