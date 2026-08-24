<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bouquet_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('label', 100);
            $table->unsignedInteger('min_sheets');
            $table->unsignedInteger('max_sheets')->nullable();
            $table->decimal('service_price', 15, 2)->default(0);
            $table->boolean('is_custom')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'code']);
            $table->index(['product_id', 'is_active', 'min_sheets']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bouquet_sizes');
    }
};
