<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_item_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('bouquet_size_id')->nullable()->index()->after('product_id');
            $table->string('bouquet_size_label')->nullable()->after('bouquet_size_id');
            $table->boolean('requires_quote')->default(false)->after('bouquet_size_label');
            $table->decimal('service_price', 15, 2)->nullable()->after('requires_quote');
        });

        Schema::table('order_item_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('bouquet_size_id')->nullable()->index()->after('product_id');
            $table->string('bouquet_size_label')->nullable()->after('bouquet_size_id');
            $table->boolean('requires_quote')->default(false)->after('bouquet_size_label');
            $table->decimal('service_price', 15, 2)->nullable()->after('requires_quote');
        });
    }

    public function down(): void
    {
        Schema::table('cart_item_groups', function (Blueprint $table) {
            $table->dropIndex(['bouquet_size_id']);
            $table->dropColumn(['bouquet_size_id', 'bouquet_size_label', 'requires_quote', 'service_price']);
        });

        Schema::table('order_item_groups', function (Blueprint $table) {
            $table->dropIndex(['bouquet_size_id']);
            $table->dropColumn(['bouquet_size_id', 'bouquet_size_label', 'requires_quote', 'service_price']);
        });
    }
};
