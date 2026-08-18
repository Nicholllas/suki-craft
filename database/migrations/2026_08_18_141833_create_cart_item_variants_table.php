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
        Schema::create('cart_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity_in_bundle')->default(1);
            $table->timestamps();

            $table->unique(['cart_item_group_id', 'product_variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_item_variants');
    }
};
