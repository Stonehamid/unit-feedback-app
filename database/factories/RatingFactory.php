<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition()
    {
        return [
            'unit_id' => Unit::factory(),
            'session_id' => $this->faker->uuid(),
            'visitor_ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'komentar' => $this->faker->optional(0.7)->paragraph(),
            'status' => $this->faker->randomElement(['pending', 'dibalas', 'selesai']),
            'metadata' => ['device' => $this->faker->randomElement(['mobile', 'desktop', 'tablet'])],
            'dibalas_pada' => $this->faker->optional(0.3)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function pending()
    {
        return $this->state([
            'status' => 'pending',
        ]);
    }

    public function dibalas()
    {
        return $this->state([
            'status' => 'dibalas',
            'dibalas_pada' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}