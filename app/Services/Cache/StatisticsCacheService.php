<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\Rating;
use App\Models\Unit;
use App\Models\User;
use App\Models\Report;

class StatisticsCacheService
{
    protected $keyGenerator;
    
    public function __construct(CacheKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }
    
    public function getRatingStatistics(bool $forceRefresh = false): array
    {
        $cacheKey = $this->keyGenerator->ratingStats();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 1800, function () {
            return [
                'total' => Rating::count(),
                'average' => round(Rating::avg('rating') ?? 0, 2),
                'with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),
                'distribution' => Rating::select('rating', \DB::raw('count(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get()
                    ->pluck('count', 'rating'),
                'generated_at' => now()->toDateTimeString(),
            ];
        });
    }
    
    public function getUnitStatistics(bool $forceRefresh = false): array
    {
        $cacheKey = $this->keyGenerator->unitStats();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'total_units' => Unit::count(),
                'avg_rating_all' => round(Unit::avg('avg_rating') ?? 0, 2),
                'active_units' => Unit::where('is_active', true)->count(),
                'featured_units' => Unit::where('featured', true)->count(),
                'types_distribution' => Unit::select('type', \DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get()
                    ->pluck('count', 'type'),
                'generated_at' => now()->toDateTimeString(),
            ];
        });
    }
    
    public function getReportStatistics(bool $forceRefresh = false): array
    {
        $cacheKey = $this->keyGenerator->reportStats();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'total' => Report::count(),
                'published' => Report::where('status', 'published')->count(),
                'draft' => Report::where('status', 'draft')->count(),
                'by_type' => Report::select('type', \DB::raw('count(*) as count'))
                    ->whereNotNull('type')
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->pluck('count', 'type'),
                'by_priority' => Report::select('priority', \DB::raw('count(*) as count'))
                    ->whereNotNull('priority')
                    ->groupBy('priority')
                    ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
                    ->get()
                    ->pluck('count', 'priority'),
                'generated_at' => now()->toDateTimeString(),
            ];
        });
    }
    
    public function getDashboardStatistics(bool $forceRefresh = false): array
    {
        $cacheKey = $this->keyGenerator->dashboardStats();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 1800, function () {
            $today = now()->today();
            $startOfWeek = now()->startOfWeek();
            $startOfMonth = now()->startOfMonth();
            
            return [
                'total_units' => Unit::count(),
                'total_ratings' => Rating::count(),
                'total_users' => User::count(),
                'total_reports' => Report::count(),
                
                'today_ratings' => Rating::whereDate('created_at', $today)->count(),
                'today_users' => User::whereDate('created_at', $today)->count(),
                
                'week_ratings' => Rating::where('created_at', '>=', $startOfWeek)->count(),
                'month_units' => Unit::where('created_at', '>=', $startOfMonth)->count(),
                
                'avg_unit_rating' => round(Unit::avg('avg_rating') ?? 0, 2),
                'top_unit' => Unit::orderBy('avg_rating', 'desc')->first(['id', 'name', 'avg_rating']),
                
                'generated_at' => now()->toDateTimeString(),
                'cached' => true,
            ];
        });
    }
    
    public function getTrendStatistics(int $days = 30): array
    {
        $cacheKey = $this->keyGenerator->ratingTrend($days);
        
        return Cache::remember($cacheKey, 3600, function () use ($days) {
            $startDate = now()->subDays($days);
            
            $ratingTrend = Rating::selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(rating) as average')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            $userGrowth = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return [
                'rating_trend' => $ratingTrend,
                'user_growth' => $userGrowth,
                'period_days' => $days,
                'generated_at' => now()->toDateTimeString(),
            ];
        });
    }
    
    public function invalidateAllStatistics(): void
    {
        $keys = [
            $this->keyGenerator->ratingStats(),
            $this->keyGenerator->unitStats(),
            $this->keyGenerator->reportStats(),
            $this->keyGenerator->dashboardStats(),
            $this->keyGenerator->userStats(),
        ];
        
        // Also invalidate trend caches
        for ($i = 7; $i <= 90; $i += 7) {
            $keys[] = $this->keyGenerator->ratingTrend($i);
        }
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
    
    public function getCacheMetrics(): array
    {
        $keys = [
            'dashboard_stats' => $this->keyGenerator->dashboardStats(),
            'rating_stats' => $this->keyGenerator->ratingStats(),
            'unit_stats' => $this->keyGenerator->unitStats(),
            'report_stats' => $this->keyGenerator->reportStats(),
            'user_stats' => $this->keyGenerator->userStats(),
        ];
        
        $metrics = [];
        foreach ($keys as $name => $key) {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                $metrics[$name] = [
                    'exists' => true,
                    'size' => strlen(serialize($data)),
                    'generated_at' => $data['generated_at'] ?? 'unknown',
                ];
            } else {
                $metrics[$name] = ['exists' => false];
            }
        }
        
        return $metrics;
    }
}