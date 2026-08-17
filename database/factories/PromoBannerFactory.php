<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PromoBannerFactory extends Factory
{
    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'image_path' => 'promo-banners/'.fake()->uuid().'.jpg', 'link_url' => fake()->optional()->url(), 'is_active' => true, 'sort_order' => fake()->numberBetween(0, 10)];
    }
}
