<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\UnitVisit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitVisitFactory extends Factory
{
    protected $model = UnitVisit::class;

    public function definition()
    {
        $waktuMasuk = $this->faker->dateTimeBetween('-1 month', 'now');
        $waktuKeluar = $this->faker->optional(0.8)->dateTimeBetween($waktuMasuk, '+2 hours');
        
        return [
            'unit_id' => Unit::factory(),
            'session_id' => $this->faker->uuid(),
            'tanggal' => $waktuMasuk->format('Y-m-d'),
            'waktu_masuk' => $waktuMasuk,
            'waktu_keluar' => $waktuKeluar,
            'durasi_detik' => $waktuKeluar ? $waktuKeluar->getTimestamp() - $waktuMasuk->getTimestamp() : null,
            'metadata' => ['visit_type' => $this->faker->randomElement(['regular', 'appointment', 'walkin'])],
        ];
    }

    public function durasiPendek()
    {
        return $this->state(function (array $attributes) {
            $waktuMasuk = $this->faker->dateTimeBetween('-1 month', 'now');
            $waktuKeluar = (clone $waktuMasuk)->modify('+' . $this->faker->numberBetween(5, 30) . ' minutes');
            
            return [
                'waktu_masuk' => $waktuMasuk,
                'waktu_keluar' => $waktuKeluar,
                'durasi_detik' => $waktuKeluar->getTimestamp() - $waktuMasuk->getTimestamp(),
            ];
        });
    }
}