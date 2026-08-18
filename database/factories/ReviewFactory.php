<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\OrderItemGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $itemGroup = OrderItemGroup::factory()->create();

        return [
            'comment' => fake()->optional()->paragraph(),
            'customer_id' => $itemGroup->order->customer_id,
            'order_id' => $itemGroup->order_id,
            'order_item_group_id' => $itemGroup->id,
            'product_id' => $itemGroup->product_id,
            'rating' => fake()->numberBetween(1, 5),
            'reviewer_name' => $itemGroup->order->customer_name,
            'status' => ReviewStatus::PENDING,
        ];
    }
}
