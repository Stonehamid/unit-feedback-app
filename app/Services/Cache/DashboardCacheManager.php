<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Unit;
use App\Models\Rating;

class DashboardCacheManager
{
    protected $keyGenerator;
    
    public function __construct(CacheKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }
    
    public function getDashboardStats(bool $forceRefresh = false): array
    {
        $cacheKey = $this->keyGenerator->dashboardStats();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 3600, function () {
            return $this->generateDashboardStats();
        });
    }
    
    public function getRoleDistribution(bool $forceRefresh = false)
    {
        $cacheKey = $this->keyGenerator->roleDistribution();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 7200, function () {
            return User::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get()
                ->pluck('count', 'role');
        });
    }
    
    public function getUnitTypeDistribution(bool $forceRefresh = false)
    {
        $cacheKey = $this->keyGenerator->unitTypeDistribution();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 7200, function () {
            return Unit::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->orderBy('count', 'desc')
                ->get();
        });
    }
    
    public function getRatingDistribution(bool $forceRefresh = false)
    {
        $cacheKey = $this->keyGenerator->ratingDistribution();
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 3600, function () {
            return Rating::select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->get();
        });
    }
    
    public function invalidateDashboardCache(): void
    {
        $keys = [
            $this->keyGenerator->dashboardStats(),
            $this->keyGenerator->roleDistribution(),
            $this->keyGenerator->unitTypeDistribution(),
            $this->keyGenerator->ratingDistribution(),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
    
    public function getCacheStatus(): array
    {
        $keys = [
            'dashboard_stats' => $this->keyGenerator->dashboardStats(),
            'role_distribution' => $this->keyGenerator->roleDistribution(),
            'unit_type_distribution' => $this->keyGenerator->unitTypeDistribution(),
            'rating_distribution' => $this->keyGenerator->ratingDistribution(),
        ];
        
        $status = [];
        foreach ($keys as $name => $key) {
            $status[$name] = [
                'key' => $key,
                'exists' => Cache::has($key),
                'ttl' => Cache::get($key . ':ttl', 'unknown'),
            ];
        }
        
        return $status;
    }
    
    private function generateDashboardStats(): array
    {
        // Basic stats calculation
        $today = now()->today();
        $startOfWeek = now()->startOfWeek();
        $startOfMonth = now()->startOfMonth();
        
        return [
            'total_units' => Unit::count(),
            'total_ratings' => Rating::count(),
            'total_users' => User::count(),
            
            'today_ratings' => Rating::whereDate('created_at', $today)->count(),
            'today_users' => User::whereDate('created_at', $today)->count(),
            
            'week_ratings' => Rating::where('created_at', '>=', $startOfWeek)->count(),
            'month_units' => Unit::where('created_at', '>=', $startOfMonth)->count(),
            
            'avg_unit_rating' => round(Unit::avg('avg_rating') ?? 0, 2),
            'top_unit' => Unit::orderBy('avg_rating', 'desc')->first(['id', 'name', 'avg_rating']),
            
            'cache_timestamp' => now()->toDateTimeString(),
        ];
    }
}