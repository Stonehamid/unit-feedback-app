<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        // Build cache key berdasarkan filters
        $cacheKey = 'units_index_' . md5(serialize($request->all()));
        
        $units = Cache::remember($cacheKey, 300, function () use ($request) { // 5 menit cache
            $query = Unit::withAvg('ratings', 'rating')
                ->withCount(['ratings', 'messages'])
                ->latest();
            
            // Apply filters
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            if ($request->has('min_rating')) {
                $query->having('ratings_avg_rating', '>=', $request->min_rating);
            }
            
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%")
                      ->orWhere('officer_name', 'like', "%{$search}%");
                });
            }
            
            $perPage = $request->get('per_page', 10);
            return $query->paginate($perPage);
        });
        
        return $units;
    }
    
    public function show(Unit $unit)
    {
        $cacheKey = 'unit_' . $unit->id . '_show';
        
        $data = Cache::remember($cacheKey, 600, function () use ($unit) { // 10 menit cache
            $unit->load(['ratings' => function($query) {
                $query->latest()->limit(10);
            }]);
            
            $unit->loadAvg('ratings', 'rating');
            $unit->loadCount(['ratings', 'messages']);
            
            return [
                'unit' => $unit,
                'recent_messages' => $unit->messages()->latest()->limit(5)->get(),
                'stats' => [
                    'total_ratings' => $unit->ratings_count,
                    'total_messages' => $unit->messages_count,
                    'average_rating' => round($unit->ratings_avg_rating, 2),
                ]
            ];
        });
        
        return $data;
    }
}