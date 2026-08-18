<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('order_item_variants')
            ->whereNull('product_variant_id')
            ->orderBy('id')
            ->chunkById(100, function ($orderItemVariants): void {
                foreach ($orderItemVariants as $orderItemVariant) {
                    $productId = DB::table('order_item_groups')
                        ->where('id', $orderItemVariant->order_item_group_id)
                        ->value('product_id');

                    if (! $productId) {
                        continue;
                    }

                    $productVariantId = DB::table('product_variants')
                        ->where('product_id', $productId)
                        ->where('label', $orderItemVariant->variant_label)
                        ->value('id');

                    if (! $productVariantId) {
                        continue;
                    }

                    DB::table('order_item_variants')
                        ->where('id', $orderItemVariant->id)
                        ->update(['product_variant_id' => $productVariantId]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('order_item_variants')->update(['product_variant_id' => null]);
    }
};
