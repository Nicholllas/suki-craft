<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100000, 500000);
        $deliveryFee = 15000;

        return [
            'customer_email' => fake()->safeEmail(),
            'customer_id' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => '08'.fake()->numerify('##########'),
            'delivery_address' => fake()->address(),
            'delivery_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'delivery_fee' => $deliveryFee,
            'delivery_time_slot' => '12:00-15:00',
            'notes' => fake()->optional()->sentence(),
            'order_number' => 'SC-'.now()->format('Ymd').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'public_token' => fake()->uuid(),
            'status' => OrderStatus::PENDING_PAYMENT,
            'subtotal' => $subtotal,
            'total' => $subtotal + $deliveryFee,
        ];
    }
}
