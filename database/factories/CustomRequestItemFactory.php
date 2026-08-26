<?php

namespace Database\Factories;

use App\Models\CustomRequest;
use App\Models\CustomRequestItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomRequestItem>
 */
class CustomRequestItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'custom_request_id' => CustomRequest::factory(),
            'item_name' => fake()->words(2, true),
            'notes' => fake()->sentence(),
            'quantity' => fake()->numberBetween(1, 10),
        ];
    }
}
