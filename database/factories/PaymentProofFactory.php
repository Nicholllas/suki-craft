<?php

namespace Database\Factories;

use App\Enums\PaymentProofStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentProofFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'path' => 'payment-proofs/'.fake()->uuid().'.jpg',
            'uploaded_at' => now(),
            'verified_at' => null,
            'verified_by' => null,
            'status' => PaymentProofStatus::PENDING,
            'rejection_reason' => null,
        ];
    }
}
