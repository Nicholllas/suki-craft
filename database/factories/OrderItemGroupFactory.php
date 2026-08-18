<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bundle_quantity' => fake()->numberBetween(1, 3),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'subtotal' => fake()->numberBetween(100000, 500000),
        ];
    }
}
