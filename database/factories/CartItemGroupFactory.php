<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'bundle_quantity' => fake()->numberBetween(1, 3),
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
        ];
    }
}
