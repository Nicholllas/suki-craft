<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = fake()->numberBetween(100000, 300000);

        return [
            'card_message' => fake()->optional()->sentence(),
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_variant_id' => null,
            'quantity' => $quantity,
            'special_note' => fake()->optional()->sentence(),
            'subtotal' => $quantity * $unitPrice,
            'unit_price' => $unitPrice,
            'variant_label' => null,
        ];
    }
}
