<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_ingredients', function (Blueprint $table) {
            $table->decimal('quantity_needed', 12, 3)->nullable()->change();
            $table->decimal('ratio_per_unit', 12, 3)->nullable()->default(1)->after('quantity_needed');
        });
    }

    public function down(): void
    {
        DB::table('product_ingredients')->whereNull('quantity_needed')->update(['quantity_needed' => DB::raw('COALESCE(ratio_per_unit, 1)')]);

        Schema::table('product_ingredients', function (Blueprint $table) {
            $table->dropColumn('ratio_per_unit');
            $table->decimal('quantity_needed', 12, 3)->nullable(false)->change();
        });
    }
};
