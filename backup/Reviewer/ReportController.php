<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Get public reports (summary only)
     */
    public function index(Request $request)
    {
        $query = Report::with(['unit:id,name', 'admin:id,name'])
            ->where('status', 'published') // Hanya reports yang dipublish
            ->latest();
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $reports = $query->paginate(10);
        
        // Statistics for public
        $stats = [
            'total_published_reports' => Report::where('status', 'published')->count(),
            'reports_this_month' => Report::where('status', 'published')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'reports_by_type' => Report::where('status', 'published')
                ->select('type', DB::raw('count(*) as count'))
                ->whereNotNull('type')
                ->groupBy('type')
                ->get(),
        ];
        
        return [
            'reports' => $reports->map(function ($report) {
                return [
                    'id' => $report->id,
                    'title' => $report->title,
                    'excerpt' => Str::limit($report->content, 150),
                    'unit' => $report->unit->name,
                    'admin' => $report->admin->name,
                    'created_at' => $report->created_at->format('M d, Y'),
                    'type' => $report->type,
                ];
            }),
            'stats' => $stats,
            'pagination' => [
                'total' => $reports->total(),
                'per_page' => $reports->perPage(),
                'current_page' => $reports->currentPage(),
            ],
        ];
    }
    
    /**
     * View single report (public)
     */
    public function show($id)
    {
        $report = Report::with(['unit', 'admin:id,name'])
            ->where('status', 'published')
            ->findOrFail($id);
        
        // Related reports
        $relatedReports = Report::where('unit_id', $report->unit_id)
            ->where('id', '!=', $report->id)
            ->where('status', 'published')
            ->with('unit:id,name')
            ->latest()
            ->limit(3)
            ->get();
        
        // Unit statistics for context
        $unitStats = [
            'total_ratings' => $report->unit->ratings()->count(),
            'average_rating' => $report->unit->avg_rating,
            'total_messages' => $report->unit->messages()->count(),
        ];
        
        return [
            'report' => [
                'id' => $report->id,
                'title' => $report->title,
                'content' => $report->content,
                'type' => $report->type,
                'priority' => $report->priority,
                'created_at' => $report->created_at->format('F j, Y'),
                'updated_at' => $report->updated_at->format('F j, Y'),
            ],
            'unit' => [
                'id' => $report->unit->id,
                'name' => $report->unit->name,
                'type' => $report->unit->type,
                'location' => $report->unit->location,
                'officer_name' => $report->unit->officer_name,
            ],
            'admin' => $report->admin->name,
            'related_reports' => $relatedReports,
            'unit_statistics' => $unitStats,
        ];
    }
    
    /**
     * Get reports by unit (public)
     */
    public function unitReports($unitId)
    {
        $unit = Unit::findOrFail($unitId);
        
        $reports = Report::where('unit_id', $unitId)
            ->where('status', 'published')
            ->with('admin:id,name')
            ->latest()
            ->paginate(10);
        
        return [
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
            ],
            'reports' => $reports,
            'summary' => [
                'total_reports' => $reports->total(),
                'published_this_year' => Report::where('unit_id', $unitId)
                    ->where('status', 'published')
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
        ];
    }
    
    /**
     * Get report statistics for public
     */
    public function statistics()
    {
        $monthlyReports = Report::where('status', 'published')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $reportsByUnit = Report::where('status', 'published')
            ->selectRaw('unit_id, COUNT(*) as count')
            ->with('unit:id,name')
            ->groupBy('unit_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $reportsByType = Report::where('status', 'published')
            ->selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
        
        return [
            'monthly_trend' => $monthlyReports,
            'by_unit' => $reportsByUnit,
            'by_type' => $reportsByType,
            'total_stats' => [
                'total_published' => Report::where('status', 'published')->count(),
                'total_units_with_reports' => Report::where('status', 'published')
                    ->distinct('unit_id')
                    ->count('unit_id'),
                'average_reports_per_unit' => round(
                    Report::where('status', 'published')->count() / 
                    max(Report::where('status', 'published')->distinct('unit_id')->count('unit_id'), 1),
                    2
                ),
            ]
        ];
    }
}