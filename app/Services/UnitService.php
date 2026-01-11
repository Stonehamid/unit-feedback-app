<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\Rating;

class UnitService
{
    /**
     * Menghitung dan memperbarui rating rata-rata sebuah unit.
     * 
     * @param \App\Models\Unit $unit Objek unit yang akan diperbarui
     * @return void
     */
    public function updateAverageRating(Unit $unit): void
    {
        // Kita sudah punya objek unit, tidak perlu cari lagi
        $averageRating = $unit->ratings()->avg('rating') ?? 0;
        
        // Update langsung menggunakan objek yang ada
        $unit->update(['average_rating' => $averageRating]);
    }
}