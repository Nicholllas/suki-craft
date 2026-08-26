<?php

namespace Database\Factories;

use App\Enums\CustomRequestStatus;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomRequest>
 */
class CustomRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'additional_notes' => fake()->sentence(),
            'budget_max' => 500000,
            'budget_min' => 250000,
            'customer_id' => Customer::factory(),
            'item_source' => 'sukicraft_purchases',
            'needed_date' => now()->addWeek()->toDateString(),
            'product_id' => Product::factory(),
            'request_kind' => 'full_bouquet',
            'request_number' => fake()->unique()->numerify('CR#####'),
            'status' => CustomRequestStatus::WAITING_REVIEW,
            'wrapping_preference' => fake()->word(),
        ];
    }
}
