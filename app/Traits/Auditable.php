<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'trashed')) {
                $model->deleted_by = Auth::id();
                $model->save();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(\App\Models\User::class, 'deleted_by');
    }

    public function getAuditLog()
    {
        return [
            'created' => [
                'by' => $this->creator?->name,
                'at' => $this->created_at,
            ],
            'updated' => [
                'by' => $this->updater?->name,
                'at' => $this->updated_at,
            ],
            'deleted' => $this->deleted_at ? [
                'by' => $this->deleter?->name,
                'at' => $this->deleted_at,
            ] : null,
        ];
    }
}