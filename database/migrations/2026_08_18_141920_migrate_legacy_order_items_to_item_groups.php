<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('order_items')->orderBy('id')->chunkById(100, function ($items): void {
            foreach ($items as $item) {
                $groupId = DB::table('order_item_groups')->insertGetId([
                    'bundle_quantity' => $item->quantity,
                    'card_message' => $item->card_message,
                    'created_at' => $item->created_at,
                    'legacy_order_item_id' => $item->id,
                    'order_id' => $item->order_id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'special_note' => $item->special_note,
                    'subtotal' => $item->subtotal,
                    'updated_at' => $item->updated_at,
                ]);

                if (! $item->product_variant_id) {
                    continue;
                }

                $basePrice = (float) DB::table('products')->where('id', $item->product_id)->value('base_price');
                DB::table('order_item_variants')->insert([
                    'created_at' => $item->created_at,
                    'line_subtotal' => $item->subtotal - ($basePrice * $item->quantity),
                    'order_item_group_id' => $groupId,
                    'quantity_in_bundle' => 1,
                    'unit_price' => (float) $item->unit_price - $basePrice,
                    'updated_at' => $item->updated_at,
                    'variant_label' => $item->variant_label ?? 'Buket pilihan',
                ]);
            }
        });
    }

    public function down(): void {}
};
