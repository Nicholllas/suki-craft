<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'Buket Bunga', 'name' => 'Peach Garden', 'price' => 185000, 'featured' => true, 'variants' => [['Small', -25000], ['Medium', 0], ['Large', 60000]]],
            ['category' => 'Buket Wisuda', 'name' => 'Blooming Graduate', 'price' => 145000, 'featured' => true, 'variants' => [['Standard', 0], ['Deluxe', 50000]]],
            ['category' => 'Buket Ulang Tahun', 'name' => 'Pink Celebration', 'price' => 165000, 'featured' => false, 'variants' => [['Regular', 0], ['Premium', 45000]]],
        ];

        foreach ($products as $item) {
            $category = Category::query()->where('name', $item['category'])->firstOrFail();
            $product = Product::updateOrCreate(['slug' => Str::slug($item['name'])], [
                'base_price' => $item['price'],
                'category_id' => $category->id,
                'description' => "Buket {$item['name']} yang dirangkai khusus untuk momen spesial.",
                'is_active' => true,
                'is_featured' => $item['featured'],
                'name' => $item['name'],
                'price' => $item['price'],
            ]);

            foreach ($item['variants'] as [$label, $adjustment]) {
                $product->variants()->updateOrCreate(['label' => $label], ['is_active' => true, 'price_adjustment' => $adjustment, 'sku' => Str::upper(Str::slug($item['name'].'-'.$label))]);
            }
        }
    }
}
