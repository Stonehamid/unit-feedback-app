<?php

namespace App\Services\Admin;

use App\Models\Rating;
use App\Models\Report;
use App\Models\Unit;
use App\Models\UnitVisit;
use Carbon\Carbon;

class ChartService
{
    public function getVisitationTrend(string $period = 'month'): array
    {
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = $endDate->copy()->subDays(6);
                $format = 'D';
                $step = '1 day';
                break;
            case 'month':
                $startDate = $endDate->copy()->subDays(29);
                $format = 'M d';
                $step = '1 day';
                break;
            case 'year':
                $startDate = $endDate->copy()->subMonths(11);
                $format = 'M Y';
                $step = '1 month';
                break;
            default:
                $startDate = $endDate->copy()->subDays(29);
                $format = 'M d';
                $step = '1 day';
        }
        
        $labels = [];
        $data = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $labels[] = $current->format($format);
            
            if ($period === 'year') {
                $count = UnitVisit::whereYear('tanggal', $current->year)
                    ->whereMonth('tanggal', $current->month)
                    ->count();
            } else {
                $count = UnitVisit::whereDate('tanggal', $current->toDateString())->count();
            }
            
            $data[] = $count;
            $current->add($step);
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'period' => $period,
            'total' => array_sum($data)
        ];
    }
    
    public function getRatingTrend(string $period = 'month'): array
    {
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'week':
                $startDate = $endDate->copy()->subDays(6);
                $format = 'D';
                $step = '1 day';
                break;
            case 'month':
                $startDate = $endDate->copy()->subDays(29);
                $format = 'M d';
                $step = '1 day';
                break;
            case 'year':
                $startDate = $endDate->copy()->subMonths(11);
                $format = 'M Y';
                $step = '1 month';
                break;
            default:
                $startDate = $endDate->copy()->subDays(29);
                $format = 'M d';
                $step = '1 day';
        }
        
        $labels = [];
        $data = [];
        $current = $startDate->copy();
        
        while ($current <= $endDate) {
            $labels[] = $current->format($format);
            
            if ($period === 'year') {
                $ratings = Rating::whereYear('created_at', $current->year)
                    ->whereMonth('created_at', $current->month)
                    ->has('scores')
                    ->with('scores')
                    ->get();
            } else {
                $ratings = Rating::whereDate('created_at', $current->toDateString())
                    ->has('scores')
                    ->with('scores')
                    ->get();
            }
            
            $average = $ratings->avg('rata_rata') ?? 0;
            $data[] = round($average, 1);
            $current->add($step);
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'period' => $period,
            'average' => round(array_sum($data) / count($data), 1)
        ];
    }
    
    public function getUnitPerformance(): array
    {
        $units = Unit::withCount(['ratings', 'visits'])
            ->has('ratings')
            ->with(['ratings' => function ($query) {
                $query->has('scores')->with('scores');
            }])
            ->limit(10)
            ->get()
            ->map(function ($unit) {
                $averageRating = $unit->ratings->avg('rata_rata') ?? 0;
                $visitCount = $unit->visits_count;
                $ratingCount = $unit->ratings_count;
                
                $performanceScore = ($averageRating / 5 * 0.6) + 
                                  (min($ratingCount, 100) / 100 * 0.2) + 
                                  (min($visitCount, 500) / 500 * 0.2);
                
                return [
                    'unit_id' => $unit->id,
                    'nama_unit' => $unit->nama_unit,
                    'jenis_unit' => $unit->jenis_unit,
                    'average_rating' => round($averageRating, 1),
                    'visit_count' => $visitCount,
                    'rating_count' => $ratingCount,
                    'performance_score' => round($performanceScore * 100, 1)
                ];
            })
            ->sortByDesc('performance_score')
            ->values();
        
        return [
            'units' => $units,
            'top_performer' => $units->first(),
            'average_performance' => round($units->avg('performance_score'), 1)
        ];
    }
    
    public function getReportAnalysis(): array
    {
        $reports = Report::selectRaw('
            DATE(created_at) as date,
            COUNT(*) as total,
            SUM(CASE WHEN prioritas IN ("tinggi", "kritis") THEN 1 ELSE 0 END) as high_priority,
            SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as resolved
        ')
        ->where('created_at', '>=', Carbon::now()->subDays(30))
        ->groupBy('date')
        ->orderBy('date')
        ->get();
        
        $byType = Report::groupBy('tipe')
            ->selectRaw('tipe, COUNT(*) as total')
            ->orderBy('total', 'desc')
            ->get();
        
        $byUnit = Report::with('unit')
            ->selectRaw('unit_id, COUNT(*) as total')
            ->groupBy('unit_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
        
        $resolutionTime = Report::where('status', 'selesai')
            ->whereNotNull('ditanggapi_pada')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, ditanggapi_pada)) as avg_hours')
            ->first();
        
        return [
            'daily_trend' => [
                'labels' => $reports->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d')),
                'total' => $reports->pluck('total'),
                'high_priority' => $reports->pluck('high_priority'),
                'resolved' => $reports->pluck('resolved')
            ],
            'by_type' => $byType,
            'by_unit' => $byUnit,
            'stats' => [
                'total_last_30_days' => $reports->sum('total'),
                'high_priority_rate' => $reports->sum('total') > 0 
                    ? round($reports->sum('high_priority') / $reports->sum('total') * 100, 1) 
                    : 0,
                'resolution_rate' => $reports->sum('total') > 0 
                    ? round($reports->sum('resolved') / $reports->sum('total') * 100, 1) 
                    : 0,
                'avg_resolution_hours' => round($resolutionTime->avg_hours ?? 0, 1)
            ]
        ];
    }
}