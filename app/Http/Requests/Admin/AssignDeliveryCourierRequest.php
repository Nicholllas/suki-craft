<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignDeliveryCourierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'courier_id' => ['required', 'integer', Rule::exists('couriers', 'id')->where('is_active', true)],
        ];
    }
}
