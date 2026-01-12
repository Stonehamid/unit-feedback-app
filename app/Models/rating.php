<?php

namespace App\Models;

use Database\Factories\RatingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
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
     * Get the unit that owns the rating
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Scope untuk rating dengan komentar
     */
    public function scopeWithComments($query)
    {
        return $query->whereNotNull('comment')->where('comment', '!=', '');
    }

    /**
     * Scope untuk rating bintang tertentu
     */
    public function scopeWithStars($query, $stars)
    {
        return $query->where('rating', $stars);
    }

    protected static function newFactory(): Factory
    {
        return RatingFactory::new();
    }
}