<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'unit_id',
        'session_id',
        'visitor_ip',
        'user_agent',
        'komentar',
        'status',
        'metadata',
        'dibalas_pada',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dibalas_pada' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scores()
    {
        return $this->hasMany(RatingScore::class);
    }

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id', 'session_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDibalas($query)
    {
        return $query->where('status', 'dibalas');
    }

    public function scopeUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function getRataRataAttribute()
    {
        return $this->scores()->avg('skor');
    }
}