<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Unit;
use App\Models\Rating;

class DashboardCacheService
{
    public function getCachedStats(string $cacheKey = null): array
    {
        $cacheKey = $cacheKey ?? 'admin_dashboard_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            $dashboardService = new DashboardService();
            
            $roleDistribution = Cache::remember('role_distribution', 3600, function () {
                return User::select('role', \DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role');
            });
            
            $unitDistribution = Cache::remember('unit_type_distribution', 7200, function () {
                return Unit::select('type', \DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get();
            });
            
            $ratingDistribution = Cache::remember('rating_distribution', 3600, function () {
                return Rating::select('rating', \DB::raw('count(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get();
            });
            
            return [
                'stats' => $dashboardService->getBasicStats(),
                'distributions' => [
                    'roles' => $roleDistribution,
                    'unit_types' => $unitDistribution,
                    'ratings' => $ratingDistribution,
                ],
                'last_updated' => now()->toDateTimeString(),
                'cached' => true,
            ];
        });
    }
    
    public function clearCache(): void
    {
        Cache::forget('admin_dashboard_' . date('Y-m-d'));
        Cache::forget('role_distribution');
        Cache::forget('unit_type_distribution');
        Cache::forget('rating_distribution');
    }
}