<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    public function definition(): array
    {
        $types = ['Administration', 'Service', 'Support', 'Technical', 'Customer Service'];
        $statuses = ['OPEN', 'CLOSED', 'FULL'];

        return [
            'name' => $this->faker->company() . ' Unit',
            'officer_name' => $this->faker->name(),
            'type' => $this->faker->randomElement($types),
            'description' => $this->faker->paragraph(3),
            'location' => $this->faker->streetAddress(),
            'status' => $this->faker->randomElement($statuses),
            'photo' => null,
            'avg_rating' => $this->faker->randomFloat(2, 1, 5),
            'is_active' => $this->faker->boolean(85),
            'featured' => $this->faker->boolean(20),
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'opening_time' => $this->faker->time('07:00'),
            'closing_time' => $this->faker->time('17:00'),
            'status_changed_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}