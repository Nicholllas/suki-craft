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
        Schema::create('custom_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_number')->nullable()->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('request_kind', 30)->default('full_bouquet');
            $table->string('status', 30)->default('waiting_review')->index();
            $table->string('item_source', 30);
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('wrapping_preference')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('reference_image_path')->nullable();
            $table->json('base_configuration')->nullable();
            $table->date('needed_date')->index();
            $table->decimal('quoted_price', 15, 2)->nullable();
            $table->text('quote_note')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('quote_expires_at')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('converted_to_cart_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_requests');
    }
};
