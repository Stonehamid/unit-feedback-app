<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\Cache\CacheKeyGenerator;
use Illuminate\Support\Facades\Cache;

class UserStatisticsService
{
    protected $cacheKeyGenerator;
    
    public function __construct(CacheKeyGenerator $cacheKeyGenerator = null)
    {
        $this->cacheKeyGenerator = $cacheKeyGenerator ?? new CacheKeyGenerator();
    }
    
    public function getOverallStatistics(bool $cached = true): array
    {
        $cacheKey = $this->cacheKeyGenerator->userStats();
        
        if ($cached && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)->count();
        $activeUsers = User::where('is_active', true)->count();
        
        $stats = [
            'total_users' => $totalUsers,
            'new_users_this_month' => $newUsersThisMonth,
            'active_users' => $activeUsers,
            'inactive_users' => $totalUsers - $activeUsers,
            'role_distribution' => $this->getRoleDistribution(),
            'growth_data' => $this->getGrowthData(),
            'generated_at' => now()->toDateTimeString(),
        ];
        
        if ($cached) {
            Cache::put($cacheKey, $stats, 3600); // Cache for 1 hour
        }
        
        return $stats;
    }
    
    public function getGrowthData(int $months = 6): array
    {
        $cacheKey = "users:growth:{$months}months";
        
        return Cache::remember($cacheKey, 7200, function () use ($months) {
            return User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths($months))
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->map(function ($item) {
                    return [
                        'month' => $item->month,
                        'count' => $item->count,
                    ];
                });
        });
    }
    
    public function getRoleDistribution(): array
    {
        return [
            'admin' => User::where('role', 'admin')->count(),
            'reviewer' => User::where('role', 'reviewer')->count(),
            'user' => User::where('role', 'user')->count(),
        ];
    }
    
    public function getUserActivityStats(User $user): array
    {
        return [
            'ratings_count' => $user->ratings()->count(),
            'messages_count' => $user->messages()->count(),
            'reports_count' => $user->reports()->count(),
            'last_activity' => $user->last_login_at ?? $user->updated_at,
            'account_age' => $user->created_at->diffForHumans(),
        ];
    }
    
    public function getTopActiveUsers(int $limit = 10): array
    {
        return User::withCount(['ratings', 'messages'])
            ->orderBy('ratings_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'ratings_count' => $user->ratings_count,
                    'messages_count' => $user->messages_count,
                    'total_activities' => $user->ratings_count + $user->messages_count,
                ];
            })
            ->toArray();
    }
    
    public function getRegistrationTrend(string $period = 'monthly'): array
    {
        $query = match($period) {
            'daily' => User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date'),
                
            'weekly' => User::selectRaw('YEARWEEK(created_at) as week, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('week')
                ->orderBy('week'),
                
            default => User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month'),
        };
        
        return $query->get()->toArray();
    }
    
    public function invalidateCache(): void
    {
        $keys = [
            $this->cacheKeyGenerator->userStats(),
            $this->cacheKeyGenerator->userGrowth(),
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}