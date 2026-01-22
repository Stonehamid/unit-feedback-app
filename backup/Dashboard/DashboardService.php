<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Unit;
use App\Models\Rating;
use App\Models\Message;
use App\Models\Report;
use Carbon\Carbon;

class DashboardService
{
    public function getBasicStats(): array
    {
        $today = Carbon::today();
        $startOfWeek = Carbon::now()->startOfWeek();
        $startOfMonth = Carbon::now()->startOfMonth();
        
        return [
            'total_units' => Unit::count(),
            'total_ratings' => Rating::count(),
            'total_messages' => Message::count(),
            'total_reports' => Report::count(),
            'total_users' => User::count(),
            
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
    }
}