<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Rating;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'admin_dashboard_' . date('Y-m-d'); // Cache per hari
        
        $data = Cache::remember($cacheKey, 3600, function () { // 1 jam cache
            $today = Carbon::today();
            $startOfWeek = Carbon::now()->startOfWeek();
            $startOfMonth = Carbon::now()->startOfMonth();
            
            // Basic counts - cache individual counts
            $totalUnits = Cache::remember('total_units', 3600, fn() => Unit::count());
            $totalRatings = Cache::remember('total_ratings', 1800, fn() => Rating::count());
            $totalMessages = Cache::remember('total_messages', 1800, fn() => Message::count());
            $totalReports = Cache::remember('total_reports', 1800, fn() => Report::count());
            $totalUsers = Cache::remember('total_users', 3600, fn() => User::count());
            
            $stats = [
                'total_units' => $totalUnits,
                'total_ratings' => $totalRatings,
                'total_messages' => $totalMessages,
                'total_reports' => $totalReports,
                'total_users' => $totalUsers,
                
                'today_ratings' => Rating::whereDate('created_at', $today)->count(),
                'today_messages' => Message::whereDate('created_at', $today)->count(),
                'today_users' => User::whereDate('created_at', $today)->count(),
                
                'week_ratings' => Rating::where('created_at', '>=', $startOfWeek)->count(),
                'week_messages' => Message::where('created_at', '>=', $startOfWeek)->count(),
                
                'month_units' => Unit::where('created_at', '>=', $startOfMonth)->count(),
                'month_reports' => Report::where('created_at', '>=', $startOfMonth)->count(),
                
                'avg_unit_rating' => round(Unit::avg('avg_rating') ?? 0, 2),
                'top_unit' => Unit::orderBy('avg_rating', 'desc')->first(['id', 'name', 'avg_rating']),
            ];
            
            // User distribution with cache
            $roleDistribution = Cache::remember('role_distribution', 3600, function () {
                return User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role');
            });
            
            // Unit distribution with cache
            $unitDistribution = Cache::remember('unit_type_distribution', 7200, function () {
                return Unit::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get();
            });
            
            // Rating distribution with cache
            $ratingDistribution = Cache::remember('rating_distribution', 3600, function () {
                return Rating::select('rating', DB::raw('count(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get();
            });
            
            return [
                'stats' => $stats,
                'distributions' => [
                    'roles' => $roleDistribution,
                    'unit_types' => $unitDistribution,
                    'ratings' => $ratingDistribution,
                ],
                'recent_activities' => $this->getRecentActivities(10),
                'last_updated' => now()->toDateTimeString(),
                'cached' => true,
            ];
        });
        
        return $data;
    }
}