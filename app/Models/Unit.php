<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
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
        'avg_rating',
    ];

    protected $casts = [
        'avg_rating' => 'decimal:2',
    ];

    /**
     * Get all ratings for this unit
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Get all messages for this unit
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get all reports for this unit
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Scope untuk mencari unit berdasarkan type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope untuk unit dengan rating tinggi
     */
    public function scopeHighRated($query, $threshold = 4.0)
    {
        return $query->where('avg_rating', '>=', $threshold);
    }

    /**
     * Update average rating
     */
    public function updateAverageRating()
    {
        $this->avg_rating = $this->ratings()->avg('rating');
        $this->save();
    }

    protected static function newFactory(): Factory
    {
        return UnitFactory::new();
    }

    public function getIsActiveAttribute()
    {
        // Default semua unit aktif
        return $this->attributes['is_active'] ?? true;
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = $value;
    }
}