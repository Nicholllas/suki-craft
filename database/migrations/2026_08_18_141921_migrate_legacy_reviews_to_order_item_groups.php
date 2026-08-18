<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('reviews')->orderBy('id')->chunkById(100, function ($reviews): void {
            foreach ($reviews as $review) {
                $orderItemGroupId = DB::table('order_item_groups')->where('legacy_order_item_id', $review->order_item_id)->value('id');

                DB::table('reviews')->where('id', $review->id)->update(['order_item_group_id' => $orderItemGroupId]);
            }
        });
    }

    public function down(): void {}
};
