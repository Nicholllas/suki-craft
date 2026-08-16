<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        $orderItem = OrderItem::factory()->create();

        return [
            'comment' => fake()->optional()->paragraph(),
            'customer_id' => $orderItem->order->customer_id,
            'order_id' => $orderItem->order_id,
            'order_item_id' => $orderItem->id,
            'product_id' => $orderItem->product_id,
            'rating' => fake()->numberBetween(1, 5),
            'reviewer_name' => $orderItem->order->customer_name,
            'status' => ReviewStatus::PENDING,
        ];
    }
}
