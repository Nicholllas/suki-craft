<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'is_active' => true,
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
        ];
    }
}
