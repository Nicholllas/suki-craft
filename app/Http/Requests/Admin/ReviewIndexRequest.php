<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return ['status' => ['nullable', Rule::in([...array_map(fn (ReviewStatus $status): string => $status->value, ReviewStatus::cases()), 'all'])]];
    }
}
