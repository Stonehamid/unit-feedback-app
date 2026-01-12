<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        $types = ['Academic', 'Administration', 'Library', 'Laboratory', 'Cafeteria', 'Sports', 'Health'];
        
        return [
            'name' => $this->faker->company() . ' ' . $this->faker->randomElement(['Department', 'Office', 'Center', 'Unit']),
            'officer_name' => $this->faker->name(),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->streetAddress(),
            'photo' => $this->faker->optional()->imageUrl(640, 480, 'business'),
            'avg_rating' => $this->faker->randomFloat(2, 1, 5),
        ];
    }
}