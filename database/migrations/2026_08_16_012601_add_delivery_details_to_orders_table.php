<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('courier_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('delivery_proof_path')->nullable();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->text('cancellation_reason')->nullable();

            $table->index(['delivery_date', 'status', 'delivery_time_slot']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['delivery_date', 'status', 'delivery_time_slot']);
            $table->dropConstrainedForeignId('courier_id');
            $table->dropColumn(['delivery_proof_path', 'delivered_at', 'cancellation_reason']);
        });
    }
};
