<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Services\Report\ReportFilterService;
use App\Services\Report\ReportService;
use App\Services\Report\ReportStatisticsService;
use App\Services\Report\ReportBulkActionService;

class ReportController extends Controller
{
    protected $filterService;
    protected $reportService;
    protected $statsService;
    protected $bulkService;
    
    public function __construct(
        ReportFilterService $filterService,
        ReportService $reportService,
        ReportStatisticsService $statsService,
        ReportBulkActionService $bulkService
    ) {
        $this->filterService = $filterService;
        $this->reportService = $reportService;
        $this->statsService = $statsService;
        $this->bulkService = $bulkService;
    }
    
    public function index(Request $request)
    {
        $query = $this->filterService->buildQuery($request);
        $reports = $this->filterService->getPagination($query, $request);
        
        return [
            'reports' => $reports,
            'stats' => $this->statsService->getOverallStats(),
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
        
        $report = $this->reportService->createReport($validated);
        
        return [
            'report' => $report,
            'message' => 'Report created successfully'
        ];
    }
    
    public function show(Report $report)
    {
        return $this->reportService->getReportWithContext($report);
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
        
        $updatedReport = $this->reportService->updateReport($report, $validated);
        
        return [
            'report' => $updatedReport,
            'message' => 'Report updated successfully'
        ];
    }
    
    public function destroy(Report $report)
    {
        $this->reportService->deleteReport($report);
        
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
        
        $result = $this->bulkService->handleBulkAction(
            $request->report_ids, 
            $request->action
        );
        
        return [
            'message' => $result['message']
        ];
    }
    
    public function statistics()
    {
        return $this->statsService->getAdvancedStatistics();
    }
}