<?php

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;
use App\Models\Rating;
use App\Models\Unit;
use App\Models\User;
use App\Models\Report;

class CacheInvalidationService
{
    protected $keyGenerator;
    
    public function __construct(CacheKeyGenerator $keyGenerator)
    {
        $this->keyGenerator = $keyGenerator;
    }
    
    public function invalidateOnRatingChange(int $unitId = null): void
    {
        $keys = [
            $this->keyGenerator->ratingStats(),
            $this->keyGenerator->dashboardStats(),
        ];
        
        // Invalidate trend caches
        for ($i = 7; $i <= 90; $i += 7) {
            $keys[] = $this->keyGenerator->ratingTrend($i);
        }
        
        if ($unitId) {
            $keys[] = $this->keyGenerator->unitStats($unitId);
        }
        
        $this->invalidateKeys($keys);
    }
    
    public function invalidateOnUnitChange(int $unitId = null): void
    {
        $keys = [
            $this->keyGenerator->unitStats(),
            $this->keyGenerator->dashboardStats(),
            $this->keyGenerator->unitTypeDistribution(),
        ];
        
        if ($unitId) {
            $keys[] = $this->keyGenerator->unitStats($unitId);
        }
        
        // Invalidate all unit list caches
        $this->invalidatePattern('units:list:*');
        
        $this->invalidateKeys($keys);
    }
    
    public function invalidateOnUserChange(int $userId = null): void
    {
        $keys = [
            $this->keyGenerator->userStats(),
            $this->keyGenerator->dashboardStats(),
            $this->keyGenerator->roleDistribution(),
            $this->keyGenerator->userGrowth(),
        ];
        
        $this->invalidateKeys($keys);
    }
    
    public function invalidateOnReportChange(): void
    {
        $keys = [
            $this->keyGenerator->reportStats(),
            $this->keyGenerator->dashboardStats(),
        ];
        
        $this->invalidateKeys($keys);
    }
    
    public function invalidateAllDashboardCache(): void
    {
        $keys = [
            $this->keyGenerator->dashboardStats(),
            $this->keyGenerator->roleDistribution(),
            $this->keyGenerator->unitTypeDistribution(),
            $this->keyGenerator->ratingDistribution(),
        ];
        
        $this->invalidateKeys($keys);
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
        
        // Invalidate all trend caches
        $this->invalidatePattern('ratings:trend:*');
        $this->invalidatePattern('users:growth:*');
        
        $this->invalidateKeys($keys);
    }
    
    public function invalidateByEvent(string $event, array $data = []): void
    {
        switch ($event) {
            case 'rating.created':
            case 'rating.updated':
            case 'rating.deleted':
                $this->invalidateOnRatingChange($data['unit_id'] ?? null);
                break;
                
            case 'unit.created':
            case 'unit.updated':
            case 'unit.deleted':
                $this->invalidateOnUnitChange($data['unit_id'] ?? null);
                break;
                
            case 'user.created':
            case 'user.updated':
            case 'user.deleted':
                $this->invalidateOnUserChange($data['user_id'] ?? null);
                break;
                
            case 'report.created':
            case 'report.updated':
            case 'report.deleted':
                $this->invalidateOnReportChange();
                break;
                
            case 'cache.clear.all':
                $this->invalidateAllStatistics();
                $this->invalidateAllDashboardCache();
                break;
                
            case 'cache.clear.dashboard':
                $this->invalidateAllDashboardCache();
                break;
        }
    }
    
    public function getInvalidationRules(): array
    {
        return [
            Rating::class => [
                'created' => ['rating', 'dashboard', 'unit' => 'unit_id'],
                'updated' => ['rating', 'dashboard', 'unit' => 'unit_id'],
                'deleted' => ['rating', 'dashboard', 'unit' => 'unit_id'],
            ],
            Unit::class => [
                'created' => ['unit', 'dashboard', 'unit_type'],
                'updated' => ['unit', 'dashboard', 'unit_type'],
                'deleted' => ['unit', 'dashboard', 'unit_type'],
            ],
            User::class => [
                'created' => ['user', 'dashboard', 'role_distribution'],
                'updated' => ['user', 'dashboard', 'role_distribution'],
                'deleted' => ['user', 'dashboard', 'role_distribution'],
            ],
            Report::class => [
                'created' => ['report', 'dashboard'],
                'updated' => ['report', 'dashboard'],
                'deleted' => ['report', 'dashboard'],
            ],
        ];
    }
    
    private function invalidateKeys(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
    
    private function invalidatePattern(string $pattern): void
    {
        // This depends on your cache driver
        // For Redis:
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            $keys = $redis->keys("*{$pattern}*");
            foreach ($keys as $key) {
                Cache::forget(str_replace(config('cache.prefix'), '', $key));
            }
        }
    }
    
    public function getCacheStatus(): array
    {
        $keys = [
            'dashboard' => $this->keyGenerator->dashboardStats(),
            'ratings' => $this->keyGenerator->ratingStats(),
            'units' => $this->keyGenerator->unitStats(),
            'users' => $this->keyGenerator->userStats(),
            'reports' => $this->keyGenerator->reportStats(),
        ];
        
        $status = [];
        $totalSize = 0;
        
        foreach ($keys as $name => $key) {
            if (Cache::has($key)) {
                $data = Cache::get($key);
                $size = strlen(serialize($data));
                $totalSize += $size;
                
                $status[$name] = [
                    'cached' => true,
                    'size_kb' => round($size / 1024, 2),
                    'items' => count($data),
                    'generated' => $data['generated_at'] ?? 'unknown',
                ];
            } else {
                $status[$name] = [
                    'cached' => false,
                    'size_kb' => 0,
                    'items' => 0,
                    'generated' => 'not cached',
                ];
            }
        }
        
        return [
            'status' => $status,
            'total_size_kb' => round($totalSize / 1024, 2),
            'total_cached_items' => count(array_filter($status, fn($s) => $s['cached'])),
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}