<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'reviewer_dashboard_stats';
        
        $data = Cache::remember($cacheKey, 300, function () { // 5 menit cache
            $stats = [
                'total_units' => Unit::count(),
                'total_ratings' => Rating::count(),
                'average_rating' => round(Rating::avg('rating') ?? 0, 2),
                'total_reviews_today' => Rating::whereDate('created_at', today())->count(),
            ];
            
            $topUnits = Cache::remember('top_rated_units', 3600, function () { // 1 jam cache
                return Unit::withAvg('ratings', 'rating')
                    ->withCount('ratings')
                    ->having('ratings_count', '>', 0)
                    ->orderBy('ratings_avg_rating', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'type', 'avg_rating']);
            });
            
            $recentRatings = Rating::with('unit:id,name')
                ->latest()
                ->limit(10)
                ->get(['id', 'unit_id', 'reviewer_name', 'rating', 'comment', 'created_at']);
            
            $ratingDistribution = Rating::select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->get()
                ->pluck('count', 'rating');
            
            return [
                'stats' => $stats,
                'top_units' => $topUnits,
                'recent_ratings' => $recentRatings,
                'rating_distribution' => $ratingDistribution,
            ];
        });
        
        return $data;
    }
    
    public function topRatedUnits(Request $request)
    {
        $limit = $request->get('limit', 10);
        $cacheKey = 'top_rated_units_' . $limit;
        
        $units = Cache::remember($cacheKey, 1800, function () use ($limit) { // 30 menit cache
            return Unit::withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->having('ratings_count', '>', 0)
                ->orderBy('ratings_avg_rating', 'desc')
                ->orderBy('ratings_count', 'desc')
                ->limit($limit)
                ->get(['id', 'name', 'type', 'location', 'photo', 'avg_rating', 'ratings_count']);
        });
        
        return [
            'top_rated_units' => $units,
            'cached' => true,
            'cache_ttl' => '30 minutes'
        ];
    }
}