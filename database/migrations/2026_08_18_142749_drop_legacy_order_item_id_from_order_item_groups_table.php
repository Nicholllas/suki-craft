<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_item_groups', function (Blueprint $table) {
            $table->dropUnique(['legacy_order_item_id']);
            $table->dropColumn('legacy_order_item_id');
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_order_item_id')->nullable()->unique();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('order_item_id')->nullable();
        });
    }
};
