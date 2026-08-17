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
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('customer_phone', 25)->index();
            $table->timestamps();
            $table->index(['promotion_id', 'customer_id']);
            $table->index(['promotion_id', 'customer_phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
    }
};
