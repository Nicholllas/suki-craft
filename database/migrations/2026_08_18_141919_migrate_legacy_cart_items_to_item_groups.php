<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cart_items')->orderBy('id')->chunkById(100, function ($items): void {
            foreach ($items as $item) {
                $groupId = DB::table('cart_item_groups')->insertGetId([
                    'bundle_quantity' => $item->quantity,
                    'card_message' => $item->card_message,
                    'cart_id' => $item->cart_id,
                    'created_at' => $item->created_at,
                    'product_id' => $item->product_id,
                    'special_note' => $item->special_note,
                    'updated_at' => $item->updated_at,
                ]);

                if (! $item->product_variant_id) {
                    continue;
                }

                $basePrice = (float) DB::table('products')->where('id', $item->product_id)->value('base_price');
                DB::table('cart_item_variants')->insert([
                    'cart_item_group_id' => $groupId,
                    'created_at' => $item->created_at,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity_in_bundle' => 1,
                    'unit_price' => (float) $item->unit_price - $basePrice,
                    'updated_at' => $item->updated_at,
                ]);
            }
        });
    }

    public function down(): void {}
};
