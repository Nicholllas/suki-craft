<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'changed_by' => null,
            'note' => fake()->sentence(),
            'order_id' => Order::factory(),
            'status' => OrderStatus::PENDING_PAYMENT,
        ];
    }
}
