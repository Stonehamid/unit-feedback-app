<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition()
    {
        $jenis = $this->faker->randomElement(['kesehatan', 'akademik', 'administrasi', 'fasilitas', 'lainnya']);
        
        return [
            'kode_unit' => strtoupper($this->faker->bothify('??-###')),
            'nama_unit' => $this->faker->company() . ' ' . ucfirst($jenis),
            'deskripsi' => $this->faker->paragraph(),
            'jenis_unit' => $jenis,
            'lokasi' => $this->faker->streetAddress(),
            'gedung' => $this->faker->buildingNumber(),
            'lantai' => $this->faker->randomElement(['1', '2', '3', '4', '5']),
            'kontak_telepon' => $this->faker->phoneNumber(),
            'kontak_email' => $this->faker->companyEmail(),
            'jam_buka' => $this->faker->time('H:i:s'),
            'jam_tutup' => $this->faker->time('H:i:s'),
            'kapasitas' => $this->faker->numberBetween(10, 500),
            'status_aktif' => $this->faker->boolean(90),
            'metadata' => ['info_tambahan' => $this->faker->sentence()],
        ];
    }

    public function kesehatan()
    {
        return $this->state([
            'jenis_unit' => 'kesehatan',
            'nama_unit' => 'Unit Kesehatan ' . $this->faker->company(),
        ]);
    }

    public function akademik()
    {
        return $this->state([
            'jenis_unit' => 'akademik',
            'nama_unit' => 'Fakultas ' . $this->faker->word(),
        ]);
    }
}