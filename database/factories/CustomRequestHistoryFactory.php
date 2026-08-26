<?php

namespace Database\Factories;

use App\Enums\CustomRequestStatus;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\CustomRequestHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomRequestHistory>
 */
class CustomRequestHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_type' => 'customer',
            'customer_id' => Customer::factory(),
            'custom_request_id' => CustomRequest::factory(),
            'note' => fake()->sentence(),
            'status' => CustomRequestStatus::WAITING_REVIEW,
        ];
    }
}
