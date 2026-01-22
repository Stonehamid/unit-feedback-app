<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters = [])
    {
        foreach ($filters as $key => $value) {
            if (method_exists($this, 'scope' . ucfirst($key))) {
                $query->{$key}($value);
            } elseif ($this->isFilterableAttribute($key) && $value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }
        
        return $query;
    }

    public function scopeSearch(Builder $query, string $search = null)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            foreach ($this->getSearchableColumns() as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    public function scopeDateRange(Builder $query, string $startDate = null, string $endDate = null)
    {
        $dateColumn = $this->getDateColumn();

        if ($startDate) {
            $query->whereDate($dateColumn, '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate($dateColumn, '<=', $endDate);
        }

        return $query;
    }

    public function scopeSort(Builder $query, string $column = 'created_at', string $direction = 'desc')
    {
        $sortableColumns = $this->getSortableColumns();
        
        if (in_array($column, $sortableColumns)) {
            return $query->orderBy($column, $direction);
        }
        
        return $query->orderBy('created_at', 'desc');
    }

    protected function getSearchableColumns()
    {
        return property_exists($this, 'searchable') ? $this->searchable : ['name'];
    }

    protected function getSortableColumns()
    {
        return property_exists($this, 'sortable') ? $this->sortable : ['created_at', 'updated_at'];
    }

    protected function getDateColumn()
    {
        return property_exists($this, 'dateColumn') ? $this->dateColumn : 'created_at';
    }

    protected function isFilterableAttribute($key)
    {
        $filterable = property_exists($this, 'filterable') ? $this->filterable : [];
        return in_array($key, $filterable);
    }
}