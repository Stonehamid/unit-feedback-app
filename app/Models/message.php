<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'message',
    ];

    /**
     * Relasi banyak-ke-satu ke tabel units.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}