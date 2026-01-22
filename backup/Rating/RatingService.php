<?php

namespace App\Services\Rating;

use App\Models\Rating;
use App\Models\Unit;
use App\Services\Logging\AdminActionLogger;

class RatingService
{
    protected $logger;
    
    public function __construct(AdminActionLogger $logger = null)
    {
        $this->logger = $logger;
    }
    
    public function createRating(array $data): Rating
    {
        $rating = Rating::create($data);
        
        // Update unit's average rating
        $this->updateUnitAverageRating($rating->unit_id);
        
        // Log if logger is available
        if ($this->logger) {
            $this->logger->log('created rating', [
                'rating_id' => $rating->id,
                'unit_id' => $rating->unit_id,
                'reviewer_name' => $rating->reviewer_name,
            ]);
        }
        
        return $rating->load('unit:id,name');
    }
    
    public function updateRating(Rating $rating, array $data): Rating
    {
        $oldRating = $rating->rating;
        $rating->update($data);
        
        // Update unit average if rating value changed
        if (isset($data['rating']) && $data['rating'] != $oldRating) {
            $this->updateUnitAverageRating($rating->unit_id);
        }
        
        if ($this->logger) {
            $this->logger->log('updated rating', [
                'rating_id' => $rating->id,
                'unit_id' => $rating->unit_id,
                'old_rating' => $oldRating,
                'new_rating' => $data['rating'] ?? $rating->rating,
            ]);
        }
        
        return $rating->fresh(['unit:id,name']);
    }
    
    public function deleteRating(Rating $rating): void
    {
        $unitId = $rating->unit_id;
        $ratingData = $rating->toArray();
        
        $rating->delete();
        
        // Update unit average
        $this->updateUnitAverageRating($unitId);
        
        if ($this->logger) {
            $this->logger->log('deleted rating', [
                'rating_id' => $ratingData['id'],
                'unit_id' => $ratingData['unit_id'],
                'reviewer_name' => $ratingData['reviewer_name'],
            ]);
        }
    }
    
    public function getRatingWithContext(Rating $rating): array
    {
        $rating->load(['unit' => function($query) {
            $query->select('id', 'name', 'type', 'location', 'avg_rating');
        }]);
        
        $otherRatings = Rating::where('reviewer_name', $rating->reviewer_name)
            ->where('id', '!=', $rating->id)
            ->with('unit:id,name')
            ->latest()
            ->limit(5)
            ->get();
        
        $unitRatings = Rating::where('unit_id', $rating->unit_id)
            ->where('id', '!=', $rating->id)
            ->latest()
            ->limit(5)
            ->get();
        
        return [
            'rating' => $rating,
            'context' => [
                'other_by_reviewer' => $otherRatings,
                'other_in_unit' => $unitRatings,
            ],
            'unit_info' => [
                'total_ratings' => $rating->unit->ratings()->count(),
                'average_rating' => $rating->unit->avg_rating,
            ]
        ];
    }
    
    public function calculateStatistics(array $ratings): array
    {
        if (empty($ratings)) {
            return [
                'average' => 0,
                'total' => 0,
                'distribution' => [],
            ];
        }
        
        $total = count($ratings);
        $sum = array_sum(array_column($ratings, 'rating'));
        $average = $total > 0 ? round($sum / $total, 2) : 0;
        
        // Calculate distribution
        $distribution = array_fill(1, 5, 0);
        foreach ($ratings as $rating) {
            $star = (int) $rating['rating'];
            if ($star >= 1 && $star <= 5) {
                $distribution[$star]++;
            }
        }
        
        return [
            'average' => $average,
            'total' => $total,
            'distribution' => $distribution,
        ];
    }
    
    public function validateRatingData(array $data): array
    {
        $errors = [];
        
        // Check rating range
        if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
            $errors[] = 'Rating must be between 1 and 5';
        }
        
        // Check comment length
        if (isset($data['comment']) && strlen($data['comment']) > 1000) {
            $errors[] = 'Comment cannot exceed 1000 characters';
        }
        
        // Check unit exists
        if (isset($data['unit_id']) && !Unit::where('id', $data['unit_id'])->exists()) {
            $errors[] = 'Invalid unit selected';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
    
    private function updateUnitAverageRating(int $unitId): void
    {
        $unit = Unit::find($unitId);
        if ($unit) {
            $unit->updateAverageRating();
        }
    }
}