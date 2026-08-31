<?php

namespace Database\Factories;

use App\Models\CustomBouquetCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomBouquetCategory>
 */
class CustomBouquetCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->sentence(),
            'is_active' => true,
            'name' => fake()->unique()->words(2, true),
            'quantity_label' => null,
            'quote_threshold' => null,
            'slug' => fake()->unique()->slug(),
            'sort_order' => 0,
        ];
    }
}
