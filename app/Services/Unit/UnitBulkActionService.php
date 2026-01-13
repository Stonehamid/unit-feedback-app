<?php

namespace App\Services\Unit;

use App\Models\Unit;
use App\Services\Logging\AdminActionLogger;

class UnitBulkActionService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function handleBulkAction(array $unitIds, string $action, array $data = []): array
    {
        $units = Unit::whereIn('id', $unitIds)->get();
        $count = 0;
        
        foreach ($units as $unit) {
            switch ($action) {
                case 'activate':
                    $unit->update(['is_active' => true]);
                    $count++;
                    break;
                    
                case 'deactivate':
                    $unit->update(['is_active' => false]);
                    $count++;
                    break;
                    
                case 'feature':
                    $unit->update(['featured' => true]);
                    $count++;
                    break;
                    
                case 'unfeature':
                    $unit->update(['featured' => false]);
                    $count++;
                    break;
                    
                case 'update':
                    $unit->update($data);
                    $count++;
                    break;
            }
        }
        
        $this->logger->logBulkAction($action, $unitIds, [
            'unit_count' => $count,
            'action_data' => $data,
        ]);
        
        return [
            'count' => $count,
            'message' => "Bulk action completed. {$count} units affected.",
        ];
    }
}