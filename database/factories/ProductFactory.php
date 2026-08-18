<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $basePrice = fake()->numberBetween(100000, 300000);
        $name = fake()->unique()->words(3, true);

        return [
            'base_price' => $basePrice,
            'category_id' => Category::factory(),
            'cost_price' => fake()->numberBetween(50000, 90000),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'is_featured' => false,
            'name' => $name,
            'price' => $basePrice,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'stock' => fake()->numberBetween(1, 50),
            'weight_grams' => fake()->numberBetween(500, 1500),
        ];
    }
}
