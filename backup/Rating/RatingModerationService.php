<?php

namespace App\Services\Rating;

use App\Models\Rating;
use App\Models\Unit;
use App\Services\Logging\AdminActionLogger;

class RatingModerationService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger)
    {
        $this->logger = $logger;
    }
    
    public function approve(Rating $rating, int $adminId): Rating
    {
        $rating->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => $adminId,
        ]);
        
        $this->logger->log('approved rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'reviewer_name' => $rating->reviewer_name,
        ]);
        
        return $rating;
    }
    
    public function reject(Rating $rating, int $adminId, string $reason = null): Rating
    {
        $rating->update([
            'is_approved' => false,
            'rejected_at' => now(),
            'rejected_by' => $adminId,
            'rejection_reason' => $reason ?? 'Violates community guidelines',
        ]);
        
        $this->logger->log('rejected rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'reviewer_name' => $rating->reviewer_name,
            'reason' => $reason,
        ]);
        
        return $rating;
    }
    
    public function update(Rating $rating, array $data): Rating
    {
        $oldData = $rating->toArray();
        $rating->update($data);
        
        if (isset($data['rating'])) {
            $rating->unit->updateAverageRating();
        }
        
        $this->logger->log('updated rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'old_data' => $oldData,
            'new_data' => $data,
        ]);
        
        return $rating->fresh(['unit:id,name']);
    }
    
    public function delete(Rating $rating): void
    {
        $unit = $rating->unit;
        $ratingData = $rating->toArray();
        
        $rating->delete();
        $unit->updateAverageRating();
        
        $this->logger->log('deleted rating', [
            'rating_id' => $ratingData['id'],
            'unit_id' => $ratingData['unit_id'],
            'reviewer_name' => $ratingData['reviewer_name'],
        ]);
    }
}