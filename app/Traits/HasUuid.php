<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid
{
    protected static function bootHasUuid()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getUuidColumn()})) {
                $model->{$model->getUuidColumn()} = Str::uuid()->toString();
            }
        });
    }

    public function getUuidColumn()
    {
        return property_exists($this, 'uuidColumn') ? $this->uuidColumn : 'uuid';
    }

    public function getRouteKeyName()
    {
        return $this->getUuidColumn();
    }

    public function scopeByUuid($query, $uuid)
    {
        return $query->where($this->getUuidColumn(), $uuid);
    }

    public static function findByUuid($uuid)
    {
        return static::byUuid($uuid)->first();
    }

    public static function findByUuidOrFail($uuid)
    {
        return static::byUuid($uuid)->firstOrFail();
    }
}