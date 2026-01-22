<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class RatingCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rating_categories';

    protected $fillable = [
        'unit_id',
        'nama_kategori',
        'slug',
        'deskripsi',
        'urutan',
        'wajib_diisi',
        'status_aktif',
    ];

    protected $casts = [
        'wajib_diisi' => 'boolean',
        'status_aktif' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scores()
    {
        return $this->hasMany(RatingScore::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    public function scopeUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }
}