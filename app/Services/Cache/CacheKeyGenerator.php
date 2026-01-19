<?php

namespace App\Services\Cache;

class CacheKeyGenerator
{
    // Dashboard cache keys
    public static function dashboardStats(string $date = null): string
    {
        $date = $date ?? date('Y-m-d');
        return "dashboard:stats:{$date}";
    }
    
    public static function roleDistribution(): string
    {
        return "dashboard:role_distribution";
    }
    
    public static function unitTypeDistribution(): string
    {
        return "dashboard:unit_type_distribution";
    }
    
    public static function ratingDistribution(): string
    {
        return "dashboard:rating_distribution";
    }
    
    // Rating cache keys
    public static function ratingStats(): string
    {
        return "ratings:stats";
    }
    
    public static function ratingTrend(int $days = 30): string
    {
        return "ratings:trend:{$days}days";
    }
    
    // Unit cache keys
    public static function unitStats(int $unitId = null): string
    {
        if ($unitId) {
            return "units:stats:{$unitId}";
        }
        return "units:stats:all";
    }
    
    public static function unitList(string $filterHash): string
    {
        return "units:list:{$filterHash}";
    }
    
    // User cache keys
    public static function userStats(): string
    {
        return "users:stats";
    }
    
    public static function userGrowth(): string
    {
        return "users:growth";
    }
    
    // Report cache keys
    public static function reportStats(): string
    {
        return "reports:stats";
    }
    
    // Generate hash for filter arrays
    public static function filterHash(array $filters): string
    {
        return md5(serialize($filters));
    }
    
    // Get TTL based on cache type
    public static function getTtl(string $key): int
    {
        $ttlMap = [
            'dashboard:stats' => 3600, // 1 hour
            'dashboard:role_distribution' => 7200, // 2 hours
            'ratings:stats' => 1800, // 30 minutes
            'units:stats' => 3600,
            'users:stats' => 3600,
            'reports:stats' => 3600,
            'units:list' => 300, // 5 minutes for lists
        ];
        
        foreach ($ttlMap as $pattern => $ttl) {
            if (str_contains($key, $pattern)) {
                return $ttl;
            }
        }
        
        return 1800; // Default 30 minutes
    }
}