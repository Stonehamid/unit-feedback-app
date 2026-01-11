<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'officer_name',
        'type',
        'description',
        'location',
        'photo',
        'average_rating',
    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
    ];

    /**
     * Relasi satu-ke-banyak ke tabel ratings.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Relasi satu-ke-banyak ke tabel messages.
     */
    public function messages()
    {
        return $this->hasMany(message::class);
    }

    /**
     * Relasi satu-ke-banyak ke tabel reports.
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}