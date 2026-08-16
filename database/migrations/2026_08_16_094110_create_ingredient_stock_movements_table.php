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
        Schema::create('ingredient_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment'])->index();
            $table->decimal('quantity', 12, 3);
            $table->string('reason', 255)->nullable();
            $table->foreignId('related_order_id')->nullable()->constrained('orders')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
            $table->index(['related_order_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_stock_movements');
    }
};
