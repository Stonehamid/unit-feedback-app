<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'unit_id',
        'admin_id',
        'judul',
        'pesan',
        'tipe',
        'prioritas',
        'dibaca',
        'dibaca_pada',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'dibaca' => 'boolean',
        'dibaca_pada' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function scopeBelumDibaca($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopePrioritasTinggi($query)
    {
        return $query->where('prioritas', 'sangat_penting');
    }

    public function scopeUntukUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    public function markAsRead()
    {
        $this->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
        ]);
    }
}