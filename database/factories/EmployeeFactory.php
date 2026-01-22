<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition()
    {
        return [
            'unit_id' => Unit::factory(),
            'nama' => $this->faker->name(),
            'jabatan' => $this->faker->jobTitle(),
            'bidang' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'telepon' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement(['aktif', 'cuti', 'resign']),
            'tanggal_mulai' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'tanggal_selesai' => $this->faker->optional(0.2)->dateTimeBetween('now', '+1 year'),
            'keterangan' => $this->faker->optional()->sentence(),
        ];
    }

    public function aktif()
    {
        return $this->state([
            'status' => 'aktif',
        ]);
    }
}