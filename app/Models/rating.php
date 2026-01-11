<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'reviewer_name',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    /**
     * Relasi banyak-ke-satu ke tabel units.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}