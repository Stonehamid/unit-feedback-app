<?php

namespace App\Services\Dashboard;

use App\Models\Rating;
use App\Models\Unit;
use App\Models\User;
use App\Models\Report;
use Carbon\Carbon;

class DashboardChartService
{
    public function getRatingTrendChart(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $ratings = Rating::selectRaw('DATE(created_at) as date, COUNT(*) as count, AVG(rating) as average')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $labels = [];
        $countData = [];
        $avgData = [];
        
        // Fill missing dates with zeros
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            
            $ratingForDate = $ratings->firstWhere('date', $date);
            
            $countData[] = $ratingForDate ? $ratingForDate->count : 0;
            $avgData[] = $ratingForDate ? round($ratingForDate->average, 2) : 0;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Ratings',
                    'data' => $countData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Average Rating',
                    'data' => $avgData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }
    
    public function getUnitTypeDistributionChart(): array
    {
        $units = Unit::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $labels = $units->pluck('type')->toArray();
        $data = $units->pluck('count')->toArray();
        $backgroundColors = $this->generateColors(count($labels));
        
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $backgroundColors,
                'borderWidth' => 1,
            ]]
        ];
    }
    
    public function getRatingDistributionChart(): array
    {
        $ratings = Rating::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();
        
        $labels = $ratings->pluck('rating')->map(fn($r) => $r . ' ⭐')->toArray();
        $data = $ratings->pluck('count')->toArray();
        
        // Gradient colors from red to green (1-5 stars)
        $colors = ['#ef4444', '#f97316', '#eab308', '#84cc16', '#22c55e'];
        
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => array_slice($colors, 0, count($data)),
                'borderWidth' => 1,
            ]]
        ];
    }
    
    public function getUserGrowthChart(int $months = 6): array
    {
        $startDate = Carbon::now()->subMonths($months);
        
        $users = User::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $labels = [];
        $data = [];
        
        // Generate labels for all months
        for ($i = $months; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $labels[] = $month;
            
            $userForMonth = $users->firstWhere('month', $month);
            $data[] = $userForMonth ? $userForMonth->count : 0;
        }
        
        // Calculate cumulative sum
        $cumulativeData = [];
        $sum = 0;
        foreach ($data as $value) {
            $sum += $value;
            $cumulativeData[] = $sum;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data,
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                ],
                [
                    'label' => 'Total Users',
                    'data' => $cumulativeData,
                    'borderColor' => '#ec4899',
                    'backgroundColor' => 'rgba(236, 72, 153, 0.1)',
                ]
            ]
        ];
    }
    
    public function getReportStatusChart(): array
    {
        $reports = Report::selectRaw('status, COUNT(*) as count')
            ->whereNotNull('status')
            ->groupBy('status')
            ->get();
        
        $labels = $reports->pluck('status')->map(fn($s) => ucfirst($s))->toArray();
        $data = $reports->pluck('count')->toArray();
        
        return [
            'labels' => $labels,
            'datasets' => [[
                'data' => $data,
                'backgroundColor' => $this->generateColors(count($labels)),
            ]]
        ];
    }
    
    public function getMonthlySummary(): array
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $previousMonth = Carbon::now()->subMonth()->format('Y-m');
        
        return [
            'current_month' => [
                'ratings' => Rating::whereMonth('created_at', Carbon::now()->month)->count(),
                'users' => User::whereMonth('created_at', Carbon::now()->month)->count(),
                'units' => Unit::whereMonth('created_at', Carbon::now()->month)->count(),
                'avg_rating' => round(Rating::whereMonth('created_at', Carbon::now()->month)->avg('rating') ?? 0, 2),
            ],
            'previous_month' => [
                'ratings' => Rating::whereMonth('created_at', Carbon::now()->subMonth()->month)->count(),
                'users' => User::whereMonth('created_at', Carbon::now()->subMonth()->month)->count(),
                'units' => Unit::whereMonth('created_at', Carbon::now()->subMonth()->month)->count(),
                'avg_rating' => round(Rating::whereMonth('created_at', Carbon::now()->subMonth()->month)->avg('rating') ?? 0, 2),
            ]
        ];
    }
    
    private function generateColors(int $count): array
    {
        $colors = [
            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
            '#14b8a6', '#f43f5e', '#8b5cf6', '#06b6d4', '#3b82f6'
        ];
        
        return array_slice($colors, 0, $count);
    }
}