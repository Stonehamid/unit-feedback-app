<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition()
    {
        return [
            'unit_id' => $this->faker->optional(0.8)->randomElement(Unit::all()),
            'session_id' => $this->faker->uuid(),
            'visitor_ip' => $this->faker->ipv4(),
            'judul' => $this->faker->sentence(),
            'deskripsi' => $this->faker->paragraphs(2, true),
            'tipe' => $this->faker->randomElement(['masalah', 'saran', 'keluhan', 'pujian', 'lainnya']),
            'prioritas' => $this->faker->randomElement(['rendah', 'sedang', 'tinggi', 'kritis']),
            'status' => $this->faker->randomElement(['baru', 'diproses', 'selesai', 'ditolak']),
            'admin_id' => $this->faker->optional(0.4)->randomElement(User::where('role', 'admin')->get()),
            'tanggapan_admin' => $this->faker->optional(0.5)->paragraph(),
            'ditanggapi_pada' => $this->faker->optional(0.4)->dateTimeBetween('-1 month', 'now'),
            'lampiran' => $this->faker->optional(0.3)->randomElements(['foto1.jpg', 'foto2.jpg', 'dokumen.pdf'], 2),
        ];
    }

    public function baru()
    {
        return $this->state([
            'status' => 'baru',
            'admin_id' => null,
            'tanggapan_admin' => null,
            'ditanggapi_pada' => null,
        ]);
    }
}