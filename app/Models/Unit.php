<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'units';

    protected $fillable = [
        'kode_unit',
        'nama_unit',
        'deskripsi',
        'jenis_unit',
        'lokasi',
        'gedung',
        'lantai',
        'kontak_telepon',
        'kontak_email',
        'jam_buka',
        'jam_tutup',
        'kapasitas',
        'status_aktif',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'jam_buka' => 'datetime:H:i:s',
        'jam_tutup' => 'datetime:H:i:s',
        'status_aktif' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    public function ratingCategories()
    {
        return $this->hasMany(RatingCategory::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function visits()
    {
        return $this->hasMany(UnitVisit::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis_unit', $jenis);
    }
}