<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    public static function clearUnitsCache()
    {
        Cache::forget('top_rated_units');
        Cache::forget('unit_type_distribution');
        Cache::tags(['units'])->flush();
    }
    
    public static function clearRatingsCache()
    {
        Cache::forget('rating_distribution');
        Cache::forget('total_ratings');
        Cache::tags(['ratings'])->flush();
    }
    
    public static function clearDashboardCache()
    {
        Cache::forget('admin_dashboard_' . date('Y-m-d'));
        Cache::forget('reviewer_dashboard_stats');
    }
}