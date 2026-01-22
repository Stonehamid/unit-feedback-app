<?php

namespace Database\Factories;

use App\Models\RatingCategory;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class RatingCategoryFactory extends Factory
{
    protected $model = RatingCategory::class;

    public function definition()
    {
        $nama = $this->faker->randomElement([
            'Fasilitas', 'Pelayanan', 'Kebersihan', 'Kenyamanan',
            'Pengajaran', 'Administrasi', 'Aksesibilitas', 'Keamanan'
        ]);
        
        return [
            'unit_id' => Unit::factory(),
            'nama_kategori' => $nama,
            'slug' => strtolower($nama) . '-' . $this->faker->uuid(),
            'deskripsi' => $this->faker->sentence(),
            'urutan' => $this->faker->numberBetween(1, 10),
            'wajib_diisi' => $this->faker->boolean(80),
            'status_aktif' => $this->faker->boolean(90),
        ];
    }
}