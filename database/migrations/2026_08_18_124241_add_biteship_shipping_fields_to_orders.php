<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('destination_area_id')->nullable()->after('delivery_address');
            $table->unsignedInteger('destination_postal_code')->nullable()->after('destination_area_id');
            $table->string('courier_company')->nullable()->after('courier_id');
            $table->string('courier_service')->nullable()->after('courier_company');
            $table->string('biteship_order_id')->nullable()->unique()->after('courier_service');
            $table->string('biteship_tracking_id')->nullable()->unique()->after('biteship_order_id');
            $table->string('tracking_number')->nullable()->after('biteship_tracking_id');
            $table->string('tracking_url', 2048)->nullable()->after('tracking_number');

            $table->index(['courier_company', 'courier_service']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['courier_company', 'courier_service']);
            $table->dropUnique(['biteship_order_id']);
            $table->dropUnique(['biteship_tracking_id']);
            $table->dropColumn([
                'destination_area_id',
                'destination_postal_code',
                'courier_company',
                'courier_service',
                'biteship_order_id',
                'biteship_tracking_id',
                'tracking_number',
                'tracking_url',
            ]);
        });
    }
};
