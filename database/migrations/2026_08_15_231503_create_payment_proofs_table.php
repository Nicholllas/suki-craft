<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $proofStatuses = ['pending', 'approved', 'rejected'];

        Schema::create('payment_proofs', function (Blueprint $table) use ($proofStatuses) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('path');
            $table->timestamp('uploaded_at');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('admins')->cascadeOnUpdate()->nullOnDelete();
            $table->enum('status', $proofStatuses)->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status', 'uploaded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
