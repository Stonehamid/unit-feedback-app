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
        'status',
        'photo',
        'avg_rating',
        'is_active',
        'featured',
        'contact_email',
        'contact_phone',
        'opening_time',
        'closing_time',
        'status_changed_at'
    ];

    protected $casts = [
        'avg_rating' => 'decimal:2',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'opening_time' => 'datetime:H:i',
        'closing_time' => 'datetime:H:i',
        'status_changed_at' => 'datetime'
    ];

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeHighRated($query, $threshold = 4.0)
    {
        return $query->where('avg_rating', '>=', $threshold);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN')->where('is_active', true);
    }
    
    public function scopeClosed($query)
    {
        return $query->where('status', 'CLOSED');
    }
    
    public function scopeFull($query)
    {
        return $query->where('status', 'FULL');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true)->where('is_active', true);
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'OPEN' => 'Buka',
            'CLOSED' => 'Tutup',
            'FULL' => 'Penuh',
            default => $this->status,
        };
    }
    
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'OPEN' => 'success',
            'CLOSED' => 'danger',
            'FULL' => 'warning',
            default => 'secondary',
        };
    }

    public function getOperatingHoursAttribute()
    {
        if ($this->opening_time && $this->closing_time) {
            return date('H:i', strtotime($this->opening_time)) . ' - ' . date('H:i', strtotime($this->closing_time));
        }
        return null;
    }

    public function getIsOperatingAttribute()
    {
        if ($this->status !== 'OPEN' || !$this->is_active) {
            return false;
        }

        if (!$this->opening_time || !$this->closing_time) {
            return true;
        }

        $currentTime = now()->format('H:i:s');
        return $currentTime >= $this->opening_time && $currentTime <= $this->closing_time;
    }

    public function updateAverageRating()
    {
        $avgRating = $this->ratings()
            ->where('is_approved', true)
            ->avg('rating');

        $this->update(['avg_rating' => round($avgRating ?: 0, 2)]);
    }

    public function updateStatus($newStatus)
    {
        $this->update([
            'status' => $newStatus,
            'status_changed_at' => now()
        ]);
    }

    public function markAsFull()
    {
        $this->updateStatus('FULL');
    }

    public function markAsOpen()
    {
        $this->updateStatus('OPEN');
    }

    public function markAsClosed()
    {
        $this->updateStatus('CLOSED');
    }

    protected static function newFactory(): Factory
    {
        return UnitFactory::new();
    }
}