<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Services\Rating\RatingFilterService;
use App\Services\Rating\RatingModerationService;
use App\Services\Rating\RatingStatisticsService;
use App\Services\Rating\RatingBulkActionService;

class RatingController extends Controller
{
    protected $filterService;
    protected $moderationService;
    protected $statsService;
    protected $bulkService;
    
    public function __construct(
        RatingFilterService $filterService,
        RatingModerationService $moderationService,
        RatingStatisticsService $statsService,
        RatingBulkActionService $bulkService
    ) {
        $this->filterService = $filterService;
        $this->moderationService = $moderationService;
        $this->statsService = $statsService;
        $this->bulkService = $bulkService;
    }
    
    public function index(Request $request)
    {
        $query = $this->filterService->buildQuery($request);
        $ratings = $this->filterService->getPagination($query, $request);
        
        return [
            'ratings' => $ratings,
            'stats' => $this->statsService->getOverallStats(),
            'filters' => $request->all(),
        ];
    }
    
    public function show(Rating $rating)
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
        
        return [
            'rating' => $rating,
            'context' => [
                'other_by_reviewer' => $otherRatings,
            ],
        ];
    }
    
    public function update(Request $request, Rating $rating)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'reviewer_name' => 'sometimes|string|max:255',
            'is_approved' => 'sometimes|boolean',
            'admin_notes' => 'nullable|string|max:500',
        ]);
        
        $updatedRating = $this->moderationService->update($rating, $validated);
        
        return [
            'rating' => $updatedRating,
            'message' => 'Rating updated successfully'
        ];
    }
    
    public function destroy(Rating $rating)
    {
        $this->moderationService->delete($rating);
        
        return [
            'message' => 'Rating deleted successfully'
        ];
    }
    
    public function approve(Rating $rating)
    {
        $updatedRating = $this->moderationService->approve($rating, auth()->id());
        
        return [
            'rating' => $updatedRating,
            'message' => 'Rating approved successfully'
        ];
    }
    
    public function reject(Rating $rating)
    {
        $reason = request('reason', 'Violates community guidelines');
        $updatedRating = $this->moderationService->reject($rating, auth()->id(), $reason);
        
        return [
            'rating' => $updatedRating,
            'message' => 'Rating rejected successfully'
        ];
    }
    
    public function bulkAction(Request $request)
    {
        $result = $this->bulkService->handleBulkAction($request);
        
        return [
            'message' => $result['message'],
            'count' => $result['count'],
        ];
    }
    
    public function statistics()
    {
        return $this->statsService->getAdvancedStatistics();
    }
}