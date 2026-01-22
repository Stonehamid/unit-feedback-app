<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'unit_id' => Unit::factory(),
            'admin_id' => User::factory(),
            'judul' => $this->faker->sentence(),
            'pesan' => $this->faker->paragraphs(3, true),
            'tipe' => $this->faker->randomElement(['saran', 'instruksi', 'pengumuman', 'lainnya']),
            'prioritas' => $this->faker->randomElement(['biasa', 'penting', 'sangat_penting']),
            'dibaca' => $this->faker->boolean(60),
            'dibaca_pada' => $this->faker->optional(0.6)->dateTimeBetween('-1 month', 'now'),
            'metadata' => ['tags' => $this->faker->words(3)],
        ];
    }

    public function belumDibaca()
    {
        return $this->state([
            'dibaca' => false,
            'dibaca_pada' => null,
        ]);
    }
}