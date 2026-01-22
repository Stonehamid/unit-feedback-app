<?php

namespace App\Services\Report;

use App\Models\Report;

class ReportStatisticsService
{
    public function getOverallStats(): array
    {
        return [
            'total' => Report::count(),
            'this_month' => Report::whereMonth('created_at', now()->month)->count(),
            'by_admin' => $this->getStatsByAdmin(),
        ];
    }
    
    public function getStatsByAdmin()
    {
        return Report::selectRaw('admin_id, count(*) as count')
            ->with('admin:id,name')
            ->groupBy('admin_id')
            ->get();
    }
    
    public function getAdvancedStatistics(): array
    {
        return [
            'monthly_trend' => $this->getMonthlyTrend(),
            'by_admin' => $this->getByAdmin(),
            'by_unit' => $this->getByUnit(),
            'by_type' => $this->getByType(),
            'by_priority' => $this->getByPriority(),
            'total_stats' => $this->getTotalStats(),
        ];
    }
    
    private function getMonthlyTrend()
    {
        return Report::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
    
    private function getByAdmin()
    {
        return Report::selectRaw('admin_id, COUNT(*) as count')
            ->with('admin:id,name')
            ->groupBy('admin_id')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    private function getByUnit()
    {
        return Report::selectRaw('unit_id, COUNT(*) as count')
            ->with('unit:id,name')
            ->groupBy('unit_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
    }
    
    private function getByType()
    {
        return Report::selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
    }
    
    private function getByPriority()
    {
        return Report::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get();
    }
    
    private function getTotalStats(): array
    {
        return [
            'total' => Report::count(),
            'published' => Report::where('status', 'published')->count(),
            'draft' => Report::where('status', 'draft')->count(),
            'archived' => Report::where('status', 'archived')->count(),
        ];
    }
}