<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropCustomerForeignKey('carts');
        DB::table('carts')->whereNotNull('customer_id')->update(['customer_id' => null]);

        Schema::table('carts', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnUpdate()->cascadeOnDelete();
        });

        $this->dropCustomerForeignKey('orders');
        DB::table('orders')->whereNotNull('customer_id')->update(['customer_id' => null]);

        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    private function dropCustomerForeignKey(string $tableName): void
    {
        $foreignKeyName = $tableName.'_customer_id_foreign';

        if (DB::getDriverName() !== 'sqlite' && ! collect(Schema::getForeignKeys($tableName))->contains(fn (array $foreignKey): bool => $foreignKey['name'] === $foreignKeyName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });
    }
};
