<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CustomBouquetCategory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CustomBouquetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customBouquetCategories = [
            ['name' => 'Buket Wisuda', 'slug' => 'buket-wisuda', 'description' => 'Rangkaian untuk perayaan kelulusan.', 'quantity_label' => null, 'quote_threshold' => null, 'sort_order' => 10],
            ['name' => 'Buket Ulang Tahun', 'slug' => 'buket-ulang-tahun', 'description' => 'Rangkaian untuk kejutan ulang tahun.', 'quantity_label' => null, 'quote_threshold' => null, 'sort_order' => 20],
            ['name' => 'Buket Anniversary', 'slug' => 'buket-anniversary', 'description' => 'Rangkaian untuk momen spesial bersama pasangan.', 'quantity_label' => null, 'quote_threshold' => null, 'sort_order' => 30],
            ['name' => 'Buket Pernikahan', 'slug' => 'buket-pernikahan', 'description' => 'Rangkaian untuk akad, resepsi, atau seserahan.', 'quantity_label' => null, 'quote_threshold' => null, 'sort_order' => 40],
            ['name' => 'Buket Uang', 'slug' => 'buket-uang', 'description' => 'Buket uang dengan nominal, bentuk, dan jumlah lembar sesuai kebutuhan.', 'quantity_label' => 'Jumlah lembar', 'quote_threshold' => 46, 'sort_order' => 50],
            ['name' => 'Buket Lainnya', 'slug' => 'buket-lainnya', 'description' => 'Untuk ide buket di luar kategori yang tersedia.', 'quantity_label' => null, 'quote_threshold' => null, 'sort_order' => 99],
        ];

        foreach ($customBouquetCategories as $customBouquetCategory) {
            CustomBouquetCategory::query()->updateOrCreate(
                ['slug' => $customBouquetCategory['slug']],
                [...$customBouquetCategory, 'is_active' => true],
            );
        }

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

        $moneyBouquetCategory = CustomBouquetCategory::query()->where('slug', 'buket-uang')->firstOrFail();
        $moneyBouquetProduct = Product::query()->where('slug', 'buket-uang')->first();

        if ($moneyBouquetProduct !== null) {
            $moneyBouquetProduct->update(['custom_bouquet_category_id' => $moneyBouquetCategory->id]);
            $moneyBouquetProduct->bouquetSizes()->where('is_custom', true)->update(['is_active' => false]);
        }
    }
}
