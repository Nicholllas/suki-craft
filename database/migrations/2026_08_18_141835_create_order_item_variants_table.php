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
        Schema::create('order_item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_group_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('variant_label');
            $table->decimal('unit_price', 15, 2);
            $table->unsignedInteger('quantity_in_bundle');
            $table->decimal('line_subtotal', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_variants');
    }
};
