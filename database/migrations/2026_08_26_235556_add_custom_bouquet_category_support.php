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
        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('custom_bouquet_category_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        Schema::table('custom_requests', function (Blueprint $table): void {
            $table->foreignId('custom_bouquet_category_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->string('custom_category_name', 100)->nullable()->after('custom_bouquet_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('custom_bouquet_category_id');
            $table->dropColumn('custom_category_name');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('custom_bouquet_category_id');
        });
    }
};
