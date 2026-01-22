<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'unit_id',
        'session_id',
        'visitor_ip',
        'judul',
        'deskripsi',
        'tipe',
        'prioritas',
        'status',
        'admin_id',
        'tanggapan_admin',
        'ditanggapi_pada',
        'lampiran',
    ];

    protected $casts = [
        'lampiran' => 'array',
        'ditanggapi_pada' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function session()
    {
        return $this->belongsTo(VisitorSession::class, 'session_id', 'session_id');
    }

    public function scopeBaru($query)
    {
        return $query->where('status', 'baru');
    }

    public function scopeDiproses($query)
    {
        return $query->where('status', 'diproses');
    }

    public function scopePrioritasTinggi($query)
    {
        return $query->whereIn('prioritas', ['tinggi', 'kritis']);
    }

    public function scopeBelumDitanggapi($query)
    {
        return $query->whereNull('admin_id');
    }

    public function isBelumDitanggapi()
    {
        return $this->status === 'baru' && !$this->admin_id;
    }
}