<?php

namespace App\Services\Unit;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitFilterService
{
    public function buildQuery(Request $request)
    {
        $query = Unit::query()
            ->withCount(['ratings', 'messages', 'reports'])
            ->withAvg('ratings', 'rating');
        
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        
        return $query;
    }
    
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('min_rating')) {
            $query->having('ratings_avg_rating', '>=', $request->min_rating);
        }
        
        if ($request->has('max_rating')) {
            $query->having('ratings_avg_rating', '<=', $request->max_rating);
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }
        
        if ($request->has('officer')) {
            $query->where('officer_name', 'like', "%{$request->officer}%");
        }
        
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->has('featured')) {
            $query->where('featured', $request->featured);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('officer_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('activity')) {
            if ($request->activity === 'active') {
                $query->has('ratings', '>', 0);
            } elseif ($request->activity === 'inactive') {
                $query->doesntHave('ratings');
            }
        }

        if ($request->has('operating_now')) {
            $currentTime = now()->format('H:i:s');
            $query->where('opening_time', '<=', $currentTime)
                  ->where('closing_time', '>=', $currentTime);
        }
    }
    
    private function applySorting($query, Request $request): void
    {
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        
        switch ($orderBy) {
            case 'avg_rating':
                $query->orderBy('avg_rating', $orderDir);
                break;
            case 'ratings_count':
                $query->orderBy('ratings_count', $orderDir);
                break;
            case 'messages_count':
                $query->orderBy('messages_count', $orderDir);
                break;
            case 'name':
                $query->orderBy('name', $orderDir);
                break;
            case 'status':
                $query->orderBy('status', $orderDir);
                break;
            default:
                $query->orderBy($orderBy, $orderDir);
        }
    }
    
    public function getPagination($query, Request $request)
    {
        $perPage = $request->get('per_page', 20);
        return $query->paginate($perPage);
    }
    
    public function getFilterOptions(): array
    {
        return [
            'types' => Unit::distinct()->pluck('type'),
            'locations' => Unit::distinct()->pluck('location')->take(50),
            'statuses' => ['OPEN', 'CLOSED', 'FULL']
        ];
    }
}