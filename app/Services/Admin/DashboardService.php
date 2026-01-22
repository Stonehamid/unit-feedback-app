<?php

namespace App\Services\Admin;

use App\Models\Rating;
use App\Models\Report;
use App\Models\Unit;
use App\Models\UnitVisit;
use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    public function getDashboardStats(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();
        
        return [
            'pengunjung' => [
                'hari_ini' => UnitVisit::whereDate('tanggal', $today)->count(),
                'minggu_ini' => UnitVisit::where('tanggal', '>=', $weekStart)->count(),
                'bulan_ini' => UnitVisit::where('tanggal', '>=', $monthStart)->count(),
                'total' => UnitVisit::count()
            ],
            'rating' => [
                'hari_ini' => Rating::whereDate('created_at', $today)->count(),
                'minggu_ini' => Rating::where('created_at', '>=', $weekStart)->count(),
                'bulan_ini' => Rating::where('created_at', '>=', $monthStart)->count(),
                'total' => Rating::count(),
                'rata_rata' => Rating::has('scores')->with('scores')->get()->avg('rata_rata') ?? 0
            ],
            'laporan' => [
                'baru' => Report::where('status', 'baru')->count(),
                'diproses' => Report::where('status', 'diproses')->count(),
                'selesai' => Report::where('status', 'selesai')->count(),
                'total' => Report::count()
            ],
            'unit' => [
                'total' => Unit::count(),
                'aktif' => Unit::where('status_aktif', true)->count(),
                'non_aktif' => Unit::where('status_aktif', false)->count(),
                'per_jenis' => Unit::groupBy('jenis_unit')->selectRaw('jenis_unit, count(*) as total')->get()
            ],
            'admin' => [
                'total' => User::count(),
                'super_admin' => User::where('role', 'super_admin')->count(),
                'admin' => User::where('role', 'admin')->count()
            ]
        ];
    }
    
    public function getChartData(): array
    {
        $visitationData = $this->getVisitationTrend();
        $ratingData = $this->getRatingDistribution();
        $unitComparison = $this->getUnitComparison();
        
        return [
            'visitation_trend' => $visitationData,
            'rating_distribution' => $ratingData,
            'unit_comparison' => $unitComparison
        ];
    }
    
    public function getOverviewData(): array
    {
        return [
            'recent_ratings' => Rating::with(['unit', 'scores.category'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'pending_reports' => Report::with(['unit', 'admin'])
                ->where('status', 'baru')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'active_visits' => UnitVisit::with('unit')
                ->whereNull('waktu_keluar')
                ->where('waktu_masuk', '>=', Carbon::now()->subHours(2))
                ->orderBy('waktu_masuk', 'desc')
                ->limit(10)
                ->get(),
            'top_rated_units' => Unit::withCount(['ratings'])
                ->orderBy('ratings_count', 'desc')
                ->limit(5)
                ->get()
        ];
    }
    
    private function getVisitationTrend(): array
    {
        $data = [];
        $startDate = Carbon::now()->subDays(30);
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $count = UnitVisit::whereDate('tanggal', $date)->count();
            
            $data[] = [
                'date' => $date->format('Y-m-d'),
                'visitors' => $count
            ];
        }
        
        return $data;
    }
    
    private function getRatingDistribution(): array
    {
        $ratings = Rating::has('scores')->with('scores')->get();
        
        $distribution = [
            '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0
        ];
        
        foreach ($ratings as $rating) {
            $avg = $rating->rata_rata;
            if ($avg) {
                $rounded = round($avg);
                $distribution[$rounded]++;
            }
        }
        
        return [
            'labels' => ['1 Bintang', '2 Bintang', '3 Bintang', '4 Bintang', '5 Bintang'],
            'data' => array_values($distribution)
        ];
    }
    
    private function getUnitComparison(): array
    {
        $units = Unit::withCount(['ratings', 'visits'])
            ->orderBy('ratings_count', 'desc')
            ->limit(10)
            ->get();
        
        return [
            'labels' => $units->pluck('nama_unit')->toArray(),
            'ratings_data' => $units->pluck('ratings_count')->toArray(),
            'visits_data' => $units->pluck('visits_count')->toArray()
        ];
    }
}