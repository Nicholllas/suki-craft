<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'rating' => ['required', 'integer', 'between:1,5'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $comment = trim((string) $this->input('comment'));

        $this->merge(['comment' => $comment ?: null]);
    }
}
