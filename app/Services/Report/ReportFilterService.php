<?php

namespace App\Services\Report;

use App\Models\Report;
use Illuminate\Http\Request;

class ReportFilterService
{
    public function buildQuery(Request $request)
    {
        $query = Report::with(['admin:id,name', 'unit:id,name']);
        
        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);
        
        return $query;
    }
    
    private function applyFilters($query, Request $request): void
    {
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
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