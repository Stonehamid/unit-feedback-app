<?php

namespace App\Services\Report;

use App\Models\Report;
use App\Services\Logging\AdminActionLogger;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function createReport(array $data): Report
    {
        $data['admin_id'] = Auth::id();
        $report = Report::create($data);
        
        $this->logger->log('created report', [
            'report_id' => $report->id,
            'unit_id' => $data['unit_id'],
            'title' => $data['title'],
        ]);
        
        return $report->load(['admin:id,name', 'unit:id,name']);
    }
    
    public function updateReport(Report $report, array $data): Report
    {
        $oldData = $report->toArray();
        $report->update($data);
        
        $this->logger->log('updated report', [
            'report_id' => $report->id,
            'unit_id' => $report->unit_id,
            'changes' => array_keys($data),
        ]);
        
        return $report->fresh(['admin:id,name', 'unit:id,name']);
    }
    
    public function deleteReport(Report $report): void
    {
        $reportData = $report->toArray();
        $report->delete();
        
        $this->logger->log('deleted report', [
            'report_id' => $reportData['id'],
            'unit_id' => $reportData['unit_id'],
            'title' => $reportData['title'],
        ]);
    }
    
    public function getReportWithContext(Report $report): array
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
}