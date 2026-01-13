<?php

namespace App\Services\Report;

use App\Models\Report;
use App\Services\Logging\AdminActionLogger;

class ReportBulkActionService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function handleBulkAction(array $reportIds, string $action, array $data = []): array
    {
        $reports = Report::whereIn('id', $reportIds)->get();
        $count = 0;
        
        foreach ($reports as $report) {
            switch ($action) {
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
                    
                case 'update':
                    $report->update($data);
                    $count++;
                    break;
            }
        }
        
        $this->logger->logBulkAction($action, $reportIds, [
            'report_count' => $count,
            'action_data' => $data,
        ]);
        
        return [
            'count' => $count,
            'message' => "Bulk action completed. {$count} reports affected.",
        ];
    }
}