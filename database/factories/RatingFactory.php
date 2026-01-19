<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reviewer_name' => $this->faker->name(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->boolean(70) ? $this->faker->sentence(10) : null,
            'is_approved' => $this->faker->boolean(85),
            'approved_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'approved_by' => null,
            'rejection_reason' => null,
            'admin_notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}