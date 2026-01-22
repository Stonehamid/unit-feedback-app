<?php

namespace Database\Factories;

use App\Models\VisitorSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorSessionFactory extends Factory
{
    protected $model = VisitorSession::class;

    public function definition()
    {
        return [
            'session_id' => $this->faker->uuid(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => ['browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari'])],
            'terakhir_aktivitas' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}