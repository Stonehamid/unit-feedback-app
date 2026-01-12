<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Log admin action to file
     */
    private function logAdminAction($action, $data = [])
    {
        Log::channel('admin')->info('Admin Action: ' . $action, array_merge([
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
        ], $data));
    }
    
    public function index(Request $request)
    {
        $query = Report::with(['admin:id,name', 'unit:id,name']);
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
        
        $perPage = $request->get('per_page', 20);
        $reports = $query->paginate($perPage);
        
        $stats = [
            'total' => Report::count(),
            'this_month' => Report::whereMonth('created_at', now()->month)->count(),
            'by_admin' => Report::selectRaw('admin_id, count(*) as count')
                ->with('admin:id,name')
                ->groupBy('admin_id')
                ->get(),
        ];
        
        return [
            'reports' => $reports,
            'stats' => $stats,
            'filters' => $request->all(),
        ];
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:units,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'nullable|string|in:weekly,monthly,quarterly,annual,incident',
            'priority' => 'nullable|string|in:low,medium,high,critical',
        ]);
        
        $validated['admin_id'] = Auth::id();
        $report = Report::create($validated);
        
        $this->logAdminAction('created report', [
            'report_id' => $report->id,
            'unit_id' => $validated['unit_id'],
            'title' => $validated['title'],
        ]);
        
        return [
            'report' => $report->load(['admin:id,name', 'unit:id,name']),
            'message' => 'Report created successfully'
        ];
    }
    
    public function show(Report $report)
    {
        $report->load(['admin:id,name,email', 'unit']);
        
        $relatedReports = Report::where('unit_id', $report->unit_id)
            ->where('id', '!=', $report->id)
            ->with('admin:id,name')
            ->latest()
            ->limit(5)
            ->get();
        
        $unitStats = [
            'total_ratings' => $report->unit->ratings()->count(),
            'avg_rating' => $report->unit->avg_rating,
            'total_messages' => $report->unit->messages()->count(),
            'total_reports' => $report->unit->reports()->count(),
        ];
        
        return [
            'report' => $report,
            'context' => [
                'related_reports' => $relatedReports,
                'unit_stats' => $unitStats,
            ]
        ];
    }
    
    public function update(Request $request, Report $report)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'nullable|string|in:weekly,monthly,quarterly,annual,incident',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:draft,published,archived',
        ]);
        
        $oldData = $report->toArray();
        $report->update($validated);
        
        $this->logAdminAction('updated report', [
            'report_id' => $report->id,
            'unit_id' => $report->unit_id,
            'changes' => array_keys($validated),
            'old_data' => $oldData,
            'new_data' => $validated,
        ]);
        
        return [
            'report' => $report->fresh(['admin:id,name', 'unit:id,name']),
            'message' => 'Report updated successfully'
        ];
    }
    
    public function destroy(Report $report)
    {
        $reportData = $report->toArray();
        $report->delete();
        
        $this->logAdminAction('deleted report', [
            'report_id' => $reportData['id'],
            'unit_id' => $reportData['unit_id'],
            'title' => $reportData['title'],
            'admin_id' => $reportData['admin_id'],
        ]);
        
        return [
            'message' => 'Report deleted successfully'
        ];
    }
    
    public function bulkAction(Request $request)
    {
        $request->validate([
            'report_ids' => 'required|array',
            'report_ids.*' => 'exists:reports,id',
            'action' => 'required|in:delete,archive,publish',
        ]);
        
        $reports = Report::whereIn('id', $request->report_ids)->get();
        $count = 0;
        
        foreach ($reports as $report) {
            switch ($request->action) {
                case 'delete':
                    $report->delete();
                    $count++;
                    break;
                    
                case 'archive':
                    $report->update(['status' => 'archived']);
                    $count++;
                    break;
                    
                case 'publish':
                    $report->update(['status' => 'published']);
                    $count++;
                    break;
            }
        }
        
        $this->logAdminAction('bulk action on reports', [
            'action' => $request->action,
            'report_count' => $count,
            'report_ids' => $request->report_ids,
        ]);
        
        return [
            'message' => "Bulk action completed. {$count} reports affected."
        ];
    }
    
    public function statistics()
    {
        $monthlyReports = Report::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $byAdmin = Report::selectRaw('admin_id, COUNT(*) as count')
            ->with('admin:id,name')
            ->groupBy('admin_id')
            ->orderBy('count', 'desc')
            ->get();
        
        $byUnit = Report::selectRaw('unit_id, COUNT(*) as count')
            ->with('unit:id,name')
            ->groupBy('unit_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $byType = Report::selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
        
        $byPriority = Report::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get();
        
        return [
            'monthly_trend' => $monthlyReports,
            'by_admin' => $byAdmin,
            'by_unit' => $byUnit,
            'by_type' => $byType,
            'by_priority' => $byPriority,
            'total_stats' => [
                'total' => Report::count(),
                'published' => Report::where('status', 'published')->count(),
                'draft' => Report::where('status', 'draft')->count(),
                'archived' => Report::where('status', 'archived')->count(),
            ]
        ];
    }
}