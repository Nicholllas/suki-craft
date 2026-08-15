<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Buket Bunga',
            'Buket Wisuda',
            'Buket Ulang Tahun',
            'Buket Pernikahan',
            'Hampers',
        ];

        foreach ($categories as $name) {
            Category::updateOrCreate(['slug' => Str::slug($name)], [
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => null,
                'is_active' => true,
            ]);
        }
    }
}
