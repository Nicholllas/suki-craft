<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            [
                'email' => env('ADMIN_DEFAULT_EMAIL', 'admin@sukicraft.test'),
            ],
            [
                'is_active' => true,
                'name' => env('ADMIN_DEFAULT_NAME', 'Super Administrator'),
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'password')),
                'role' => AdminRole::SUPER_ADMIN,
            ]
        );
    }
}
