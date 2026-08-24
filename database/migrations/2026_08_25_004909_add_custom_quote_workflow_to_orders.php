<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orderStatuses = ['awaiting_quote', 'awaiting_approval', 'pending_payment', 'awaiting_verification', 'payment_confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];

        Schema::table('orders', function (Blueprint $table) use ($orderStatuses) {
            $table->text('quote_note')->nullable()->after('notes');
            $table->timestamp('quoted_at')->nullable()->after('quote_note');
            $table->timestamp('quote_expires_at')->nullable()->index()->after('quoted_at');
            $table->timestamp('quote_approved_at')->nullable()->after('quote_expires_at');
            $table->enum('status', $orderStatuses)->default('pending_payment')->change();
        });

        Schema::table('order_status_histories', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->change();
        });
    }

    public function down(): void
    {
        $orderStatuses = ['pending_payment', 'awaiting_verification', 'payment_confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];

        Schema::table('orders', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->default('pending_payment')->change();
            $table->dropIndex(['quote_expires_at']);
            $table->dropColumn(['quote_note', 'quoted_at', 'quote_expires_at', 'quote_approved_at']);
        });

        Schema::table('order_status_histories', function (Blueprint $table) use ($orderStatuses) {
            $table->enum('status', $orderStatuses)->change();
        });
    }
};
