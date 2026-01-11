<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'unit_id',
        'title',
        'content',
    ];

    /**
     * Relasi banyak-ke-satu ke tabel users (sebagai admin).
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi banyak-ke-satu ke tabel units.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}