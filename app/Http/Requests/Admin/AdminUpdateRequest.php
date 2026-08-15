<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\InteractsWithAdminAccountRules;
use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
{
    use InteractsWithAdminAccountRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->accountRules($this->route('admin'));
    }
}
