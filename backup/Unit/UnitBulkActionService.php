<?php

namespace App\Services\Unit;

use App\Models\Unit;

class UnitBulkActionService
{
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
                    
                case 'mark_open':
                    $unit->update(['status' => 'OPEN', 'status_changed_at' => now()]);
                    $count++;
                    break;
                    
                case 'mark_closed':
                    $unit->update(['status' => 'CLOSED', 'status_changed_at' => now()]);
                    $count++;
                    break;
                    
                case 'mark_full':
                    $unit->update(['status' => 'FULL', 'status_changed_at' => now()]);
                    $count++;
                    break;
                    
                case 'update':
                    $unit->update($data);
                    $count++;
                    break;
            }
        }
        
        return [
            'count' => $count,
            'message' => "Bulk action completed. {$count} units affected.",
        ];
    }
}