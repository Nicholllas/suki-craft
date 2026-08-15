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
            'awaiting_verification',
            'payment_confirmed',
            'processing',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];

        Schema::table('orders', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->default('pending_payment')->change();
        });

        Schema::table('order_status_histories', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->change();
        });
    }

    public function down(): void
    {
        $orderStatuses = [
            'pending_payment',
            'payment_confirmed',
            'processing',
            'out_for_delivery',
            'delivered',
            'cancelled',
        ];

        Schema::table('orders', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->default('pending_payment')->change();
        });

        Schema::table('order_status_histories', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->change();
        });
    }
};
