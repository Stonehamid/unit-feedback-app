<?php

namespace App\Http\Controllers\Reviewer;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    /**
     * Submit rating (public - no authentication)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit_id' => 'required|exists:units,id',
            'rating' => 'required|integer|min:1|max:5',
            'reviewer_name' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();
        
        // Default name jika kosong
        if (empty($data['reviewer_name'])) {
            $data['reviewer_name'] = 'Anonymous Reviewer';
        }
        
        // Cek apakah reviewer sudah kasih rating untuk unit ini
        // (optional: bisa dihapus kalo mau allow multiple ratings)
        $existingRating = Rating::where('unit_id', $data['unit_id'])
            ->where('reviewer_name', $data['reviewer_name'])
            ->first();
            
        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a rating for this unit',
                'existing_rating' => $existingRating
            ], 409);
        }
        
        $rating = Rating::create($data);
        
        // Update unit average rating
        $unit = Unit::find($data['unit_id']);
        $unit->updateAverageRating();
        
        return [
            'success' => true,
            'message' => 'Rating submitted successfully',
            'rating' => $rating,
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'new_average_rating' => $unit->avg_rating,
            ]
        ];
    }
    
    /**
     * Get ratings for a unit (public)
     */
    public function unitRatings($unitId, Request $request)
    {
        $unit = Unit::findOrFail($unitId);
        
        $query = Rating::where('unit_id', $unitId);
        
        // Filter by rating stars
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        // Filter by has comment
        if ($request->has('with_comments') && $request->with_comments === 'true') {
            $query->whereNotNull('comment')->where('comment', '!=', '');
        }
        
        // Ordering
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
        
        $ratings = $query->paginate(10);
        
        $ratingStats = Rating::where('unit_id', $unitId)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();
        
        return [
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name,
                'average_rating' => $unit->avg_rating,
                'total_ratings' => $unit->ratings()->count(),
            ],
            'ratings' => $ratings,
            'rating_stats' => $ratingStats,
            'summary' => $this->getRatingSummary($ratingStats),
        ];
    }
    
    /**
     * Get recent ratings across all units (public)
     */
    public function recentRatings(Request $request)
    {
        $limit = $request->get('limit', 20);
        
        $ratings = Rating::with('unit:id,name,type')
            ->latest()
            ->limit($limit)
            ->get(['id', 'unit_id', 'reviewer_name', 'rating', 'comment', 'created_at']);
        
        return [
            'recent_ratings' => $ratings,
            'total_count' => Rating::count(),
        ];
    }
    
    /**
     * Get top rated units (public)
     */
    public function topRatedUnits(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $units = Unit::withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->having('ratings_count', '>', 0)
            ->orderBy('ratings_avg_rating', 'desc')
            ->orderBy('ratings_count', 'desc')
            ->limit($limit)
            ->get(['id', 'name', 'type', 'location', 'photo']);
        
        return [
            'top_rated_units' => $units,
            'criteria' => 'Sorted by average rating and number of ratings',
        ];
    }
    
    private function getRatingSummary($ratingStats)
    {
        $total = $ratingStats->sum('count');
        
        if ($total === 0) {
            return null;
        }
        
        $summary = [];
        foreach ($ratingStats as $stat) {
            $percentage = round(($stat->count / $total) * 100, 1);
            $summary[] = [
                'stars' => $stat->rating,
                'count' => $stat->count,
                'percentage' => $percentage,
                'bar_width' => $percentage . '%',
            ];
        }
        
        return $summary;
    }
}