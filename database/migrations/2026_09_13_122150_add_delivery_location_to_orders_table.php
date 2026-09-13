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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address');
            $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            $table->unsignedInteger('delivery_distance_meters')->nullable()->after('delivery_longitude');
            $table->string('delivery_route_provider', 50)->nullable()->after('delivery_distance_meters');
            $table->timestamp('delivery_route_calculated_at')->nullable()->after('delivery_route_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_latitude',
                'delivery_longitude',
                'delivery_distance_meters',
                'delivery_route_provider',
                'delivery_route_calculated_at',
            ]);
        });
    }
};
