<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'message' => $this->faker->paragraph(2),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }
}