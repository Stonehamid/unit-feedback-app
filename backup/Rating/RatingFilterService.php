<?php

namespace App\Services\Rating;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingFilterService
{
    public function buildQuery(Request $request)
    {
        $query = Rating::with('unit:id,name,type');
        
        // Apply filters
        $this->applyFilters($query, $request);
        
        // Apply sorting
        $this->applySorting($query, $request);
        
        return $query;
    }
    
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('reviewer')) {
            $query->where('reviewer_name', 'like', "%{$request->reviewer}%");
        }
        
        if ($request->has('has_comment')) {
            if ($request->has_comment === 'yes') {
                $query->whereNotNull('comment')->where('comment', '!=', '');
            } else {
                $query->whereNull('comment')->orWhere('comment', '');
            }
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhere('reviewer_name', 'like', "%{$search}%");
            });
        }
    }
    
    private function applySorting($query, Request $request): void
    {
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
    }
    
    public function getPagination($query, Request $request)
    {
        $perPage = $request->get('per_page', 20);
        return $query->paginate($perPage);
    }
}