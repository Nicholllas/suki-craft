<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orderStatuses = [
            'pending_payment',
            'payment_confirmed',
            'processing',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];

        Schema::create('orders', function (Blueprint $table) use ($orderStatuses) {
            $table->id();
            $table->string('order_number')->unique();
            $table->uuid('public_token')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->text('delivery_address');
            $table->date('delivery_date');
            $table->string('delivery_time_slot');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('delivery_fee', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->enum('status', $orderStatuses)->default('pending_payment')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'created_at']);
            $table->index(['delivery_date', 'delivery_time_slot']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('card_message', 200)->nullable();
            $table->string('special_note', 300)->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_histories', function (Blueprint $table) use ($orderStatuses) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('status', $orderStatuses)->index();
            $table->text('note')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('admins')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
