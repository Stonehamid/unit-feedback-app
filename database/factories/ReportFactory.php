<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'unit_id' => \App\Models\Unit::factory(), 
            'admin_id' => \App\Models\User::factory(),
        ];
    }
}