<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Validation\Rule;

trait InteractsWithAdminAccountRules
{
    protected function accountRules(?Admin $admin = null): array
    {
        return [
            'email' => ['required', 'email', 'max:255', Rule::unique('admins')->ignore($admin?->id)],
            'is_active' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::enum(AdminRole::class)],
        ];
    }
}
