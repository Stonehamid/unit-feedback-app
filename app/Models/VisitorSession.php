<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    use HasFactory;

    protected $table = 'visitor_sessions';

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'metadata',
        'terakhir_aktivitas',
    ];

    protected $casts = [
        'metadata' => 'array',
        'terakhir_aktivitas' => 'datetime',
    ];

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'session_id', 'session_id');
    }

    public function visits()
    {
        return $this->hasMany(UnitVisit::class, 'session_id', 'session_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'session_id', 'session_id');
    }

    public function isActive()
    {
        return $this->terakhir_aktivitas && $this->terakhir_aktivitas->diffInMinutes(now()) < 30;
    }

    public function scopeAktif($query)
    {
        return $query->where('terakhir_aktivitas', '>=', now()->subMinutes(30));
    }
}