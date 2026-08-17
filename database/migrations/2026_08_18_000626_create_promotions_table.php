<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 15, 2);
            $table->decimal('min_purchase', 15, 2)->nullable();
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->default(1);
            $table->timestamp('starts_at');
            $table->timestamp('expires_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
