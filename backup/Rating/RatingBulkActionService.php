<?php

namespace App\Services\Rating;

use App\Models\Rating;
use App\Models\Unit;
use App\Services\Logging\AdminActionLogger;

class RatingBulkActionService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function handleBulkAction(array $ratingIds, string $action, array $data = []): array
    {
        $ratings = Rating::whereIn('id', $ratingIds)->get();
        $count = 0;
        $unitIds = [];
        
        foreach ($ratings as $rating) {
            switch ($action) {
                case 'approve':
                    $rating->update([
                        'is_approved' => true,
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                    ]);
                    $count++;
                    break;
                    
                case 'reject':
                    $rating->update([
                        'is_approved' => false,
                        'rejected_at' => now(),
                        'rejected_by' => auth()->id(),
                        'rejection_reason' => $data['reason'] ?? 'Violates community guidelines',
                    ]);
                    $count++;
                    break;
                    
                case 'delete':
                    $unitIds[] = $rating->unit_id;
                    $rating->delete();
                    $count++;
                    break;
                    
                case 'update':
                    if (!empty($data)) {
                        $rating->update($data);
                        $count++;
                        
                        if (isset($data['rating'])) {
                            $rating->unit->updateAverageRating();
                        }
                    }
                    break;
            }
        }
        
        // Update unit ratings if ratings were deleted
        if ($action === 'delete' && !empty($unitIds)) {
            $this->updateUnitsAfterBulkDelete($unitIds);
        }
        
        $this->logger->logBulkAction($action, $ratingIds, [
            'rating_count' => $count,
            'action_data' => $data,
        ]);
        
        return [
            'count' => $count,
            'message' => "Bulk action completed. {$count} ratings affected.",
        ];
    }
    
    private function updateUnitsAfterBulkDelete(array $unitIds): void
    {
        $uniqueUnitIds = array_unique($unitIds);
        
        foreach ($uniqueUnitIds as $unitId) {
            $unit = Unit::find($unitId);
            if ($unit) {
                $unit->updateAverageRating();
            }
        }
    }
    
    public function validateBulkAction(array $ratingIds, string $action): array
    {
        $ratings = Rating::whereIn('id', $ratingIds)->get();
        
        if ($ratings->count() !== count($ratingIds)) {
            return [
                'valid' => false,
                'message' => 'Some rating IDs are invalid.'
            ];
        }
        
        if ($action === 'delete') {
            $ratingsWithComments = $ratings->filter(function($rating) {
                return !empty($rating->comment);
            });
            
            return [
                'valid' => true,
                'warnings' => $ratingsWithComments->count() > 0 ? 
                    "{$ratingsWithComments->count()} ratings have comments that will be deleted." : null
            ];
        }
        
        return ['valid' => true];
    }
}