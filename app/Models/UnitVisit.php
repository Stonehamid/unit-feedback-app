<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitVisit extends Model
{
    use HasFactory;

    protected $table = 'unit_visits';

    protected $fillable = [
        'unit_id',
        'session_id',
        'tanggal',
        'waktu_masuk',
        'waktu_keluar',
        'durasi_detik',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'waktu_masuk' => 'datetime',
        'waktu_keluar' => 'datetime',
        'tanggal' => 'date',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id', 'session_id');
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal', today());
    }

    public function scopeMingguIni($query)
    {
        return $query->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('tanggal', now()->month)
                     ->whereYear('tanggal', now()->year);
    }

    public function scopeBelumKeluar($query)
    {
        return $query->whereNull('waktu_keluar');
    }

    public function getDurasiMenitAttribute()
    {
        return $this->durasi_detik ? round($this->durasi_detik / 60, 1) : null;
    }
}