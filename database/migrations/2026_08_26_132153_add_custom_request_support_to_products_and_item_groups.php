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
        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_custom_request')->default(false)->after('allow_multiple_variants');
        });

        Schema::table('cart_item_groups', function (Blueprint $table): void {
            $table->foreignId('custom_request_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });

        Schema::table('order_item_groups', function (Blueprint $table): void {
            $table->foreignId('custom_request_id')->nullable()->unique()->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_item_groups', function (Blueprint $table): void {
            $table->dropUnique(['custom_request_id']);
            $table->dropConstrainedForeignId('custom_request_id');
        });

        Schema::table('cart_item_groups', function (Blueprint $table): void {
            $table->dropUnique(['custom_request_id']);
            $table->dropConstrainedForeignId('custom_request_id');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('is_custom_request');
        });
    }
};
