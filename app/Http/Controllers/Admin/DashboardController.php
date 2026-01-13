<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardCacheService;
use App\Services\Dashboard\DashboardStatsService;

class DashboardController extends Controller
{
    protected $cacheService;
    protected $statsService;
    
    public function __construct(
        DashboardCacheService $cacheService,
        DashboardStatsService $statsService
    ) {
        $this->cacheService = $cacheService;
        $this->statsService = $statsService;
    }
    
    public function index()
    {
        return $this->cacheService->getCachedStats();
    }
    
    public function performance()
    {
        return [
            'metrics' => $this->statsService->getPerformanceMetrics(),
            'cache_status' => $this->cacheService->getCachedStats(),
        ];
    }
    
    public function clearCache()
    {
        $this->cacheService->clearCache();
        
        return [
            'message' => 'Dashboard cache cleared successfully',
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}