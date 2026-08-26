<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CustomBouquetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'buket-custom'],
            [
                'description' => null,
                'is_active' => true,
                'name' => 'Buket Custom',
            ],
        );
        $product = Product::query()->withTrashed()->firstOrCreate(
            ['slug' => 'buket-custom'],
            [
                'allow_multiple_variants' => false,
                'base_price' => 0,
                'category_id' => $category->id,
                'cost_price' => 0,
                'description' => 'Ceritakan ide buketmu, lalu kami siapkan penawaran harga yang sesuai.',
                'is_active' => true,
                'is_featured' => false,
                'name' => 'Buat Buket Custom',
                'price' => 0,
                'stock' => 0,
            ],
        );

        if ($product->trashed()) {
            $product->restore();
        }

        $product->update([
            'category_id' => $category->id,
            'is_custom_request' => true,
        ]);
    }
}
