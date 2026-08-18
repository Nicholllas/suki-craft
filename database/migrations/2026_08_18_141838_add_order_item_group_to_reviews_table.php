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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->dropUnique(['order_item_id']);
            $table->foreignId('order_item_group_id')->nullable()->after('order_item_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete()->unique();
            $table->foreignId('order_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['order_item_group_id']);
            $table->dropUnique(['order_item_group_id']);
            $table->dropColumn('order_item_group_id');
            $table->unsignedBigInteger('order_item_id')->nullable(false)->change();
            $table->unique('order_item_id');
            $table->foreign('order_item_id')->references('id')->on('order_items')->cascadeOnDelete();
        });
    }
};
