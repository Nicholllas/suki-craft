<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $productId = DB::table('products')->where('slug', 'buket-uang')->value('id');

        if (! $productId) {
            return;
        }

        $now = now();
        $sizes = [
            ['code' => 'S', 'label' => 'Small', 'min_sheets' => 1, 'max_sheets' => 6, 'service_price' => 55000, 'is_custom' => false, 'is_active' => true],
            ['code' => 'M', 'label' => 'Medium', 'min_sheets' => 7, 'max_sheets' => 15, 'service_price' => 100000, 'is_custom' => false, 'is_active' => true],
            ['code' => 'L', 'label' => 'Large', 'min_sheets' => 16, 'max_sheets' => 29, 'service_price' => 165000, 'is_custom' => false, 'is_active' => true],
            ['code' => 'XL', 'label' => 'Extra Large', 'min_sheets' => 30, 'max_sheets' => 40, 'service_price' => 180000, 'is_custom' => false, 'is_active' => true],
            ['code' => 'XXL', 'label' => 'Extra Extra Large', 'min_sheets' => 41, 'max_sheets' => 45, 'service_price' => 250000, 'is_custom' => false, 'is_active' => true],
            ['code' => 'CUSTOM', 'label' => 'Custom', 'min_sheets' => 46, 'max_sheets' => null, 'service_price' => 0, 'is_custom' => true, 'is_active' => true],
        ];

        foreach ($sizes as $size) {
            DB::table('product_bouquet_sizes')->updateOrInsert(
                ['product_id' => $productId, 'code' => $size['code']],
                [...$size, 'product_id' => $productId, 'created_at' => $now, 'updated_at' => $now],
            );
        }
    }

    public function down(): void
    {
        $productId = DB::table('products')->where('slug', 'buket-uang')->value('id');

        if ($productId) {
            DB::table('product_bouquet_sizes')->where('product_id', $productId)->whereIn('code', ['S', 'M', 'L', 'XL', 'XXL', 'CUSTOM'])->delete();
        }
    }
};
