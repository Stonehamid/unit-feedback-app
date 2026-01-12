<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    /**
     * Log admin action to file
     */
    private function logAdminAction($action, $data = [])
    {
        Log::channel('admin')->info('Admin Action: ' . $action, array_merge([
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
        ], $data));
    }

    public function index(Request $request)
    {
        $query = Rating::with('unit:id,name,type');
        
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }
        
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('reviewer')) {
            $query->where('reviewer_name', 'like', "%{$request->reviewer}%");
        }
        
        if ($request->has('has_comment')) {
            if ($request->has_comment === 'yes') {
                $query->whereNotNull('comment')->where('comment', '!=', '');
            } else {
                $query->whereNull('comment')->orWhere('comment', '');
            }
        }
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhere('reviewer_name', 'like', "%{$search}%");
            });
        }
        
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);
        
        $perPage = $request->get('per_page', 20);
        $ratings = $query->paginate($perPage);
        
        $stats = [
            'total' => Rating::count(),
            'with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),
            'average_rating' => round(Rating::avg('rating') ?? 0, 2),
            'distribution' => Rating::select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->get()
                ->pluck('count', 'rating'),
        ];
        
        return [
            'ratings' => $ratings,
            'stats' => $stats,
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
    
    public function update(Request $request, Rating $rating)
    {
        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'reviewer_name' => 'sometimes|string|max:255',
            'is_approved' => 'sometimes|boolean',
            'admin_notes' => 'nullable|string|max:500',
        ]);
        
        $oldData = $rating->toArray();
        $rating->update($validated);
        
        if (isset($validated['rating'])) {
            $rating->unit->updateAverageRating();
        }
        
        $this->logAdminAction('updated rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'reviewer_name' => $rating->reviewer_name,
            'old_data' => $oldData,
            'new_data' => $validated,
        ]);
        
        return [
            'rating' => $rating->fresh(['unit:id,name']),
            'message' => 'Rating updated successfully'
        ];
    }
    
    public function destroy(Rating $rating)
    {
        $unit = $rating->unit;
        $reviewerName = $rating->reviewer_name;
        $ratingData = $rating->toArray();
        
        $rating->delete();
        $unit->updateAverageRating();
        
        $this->logAdminAction('deleted rating', [
            'rating_id' => $ratingData['id'],
            'unit_id' => $ratingData['unit_id'],
            'reviewer_name' => $ratingData['reviewer_name'],
            'rating_value' => $ratingData['rating'],
        ]);
        
        return [
            'message' => 'Rating deleted successfully'
        ];
    }
    
    public function approve(Rating $rating)
    {
        $rating->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        
        $this->logAdminAction('approved rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'reviewer_name' => $rating->reviewer_name,
        ]);
        
        return [
            'rating' => $rating,
            'message' => 'Rating approved successfully'
        ];
    }
    
    public function reject(Rating $rating)
    {
        $rating->update([
            'is_approved' => false,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => request('reason', 'Violates community guidelines'),
        ]);
        
        $this->logAdminAction('rejected rating', [
            'rating_id' => $rating->id,
            'unit_id' => $rating->unit_id,
            'reviewer_name' => $rating->reviewer_name,
            'reason' => request('reason'),
        ]);
        
        return [
            'rating' => $rating,
            'message' => 'Rating rejected successfully'
        ];
    }
    
    public function bulkAction(Request $request)
    {
        $request->validate([
            'rating_ids' => 'required|array',
            'rating_ids.*' => 'exists:ratings,id',
            'action' => 'required|in:approve,reject,delete',
            'reason' => 'required_if:action,reject|string|max:500',
        ]);
        
        $ratings = Rating::whereIn('id', $request->rating_ids)->get();
        $count = 0;
        
        foreach ($ratings as $rating) {
            switch ($request->action) {
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
                        'rejection_reason' => $request->reason,
                    ]);
                    $count++;
                    break;
                    
                case 'delete':
                    $rating->delete();
                    $count++;
                    break;
            }
        }
        
        if ($request->action === 'delete') {
            $unitIds = $ratings->pluck('unit_id')->unique();
            foreach ($unitIds as $unitId) {
                $unit = Unit::find($unitId);
                if ($unit) {
                    $unit->updateAverageRating();
                }
            }
        }
        
        $this->logAdminAction('bulk action on ratings', [
            'action' => $request->action,
            'rating_count' => $count,
            'rating_ids' => $request->rating_ids,
        ]);
        
        return [
            'message' => "Bulk action completed. {$count} ratings affected."
        ];
    }
    
    public function statistics()
    {
        $dailyRatings = Rating::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $byUnitType = Rating::join('units', 'ratings.unit_id', '=', 'units.id')
            ->selectRaw('units.type, COUNT(*) as count')
            ->groupBy('units.type')
            ->orderBy('count', 'desc')
            ->get();
        
        $topReviewers = Rating::selectRaw('reviewer_name, COUNT(*) as count, AVG(rating) as avg_rating')
            ->groupBy('reviewer_name')
            ->having('count', '>', 1)
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $commentStats = [
            'with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),
            'without_comments' => Rating::whereNull('comment')->orWhere('comment', '')->count(),
        ];
        
        return [
            'daily_trend' => $dailyRatings,
            'by_unit_type' => $byUnitType,
            'top_reviewers' => $topReviewers,
            'comment_stats' => $commentStats,
            'total_stats' => [
                'average' => round(Rating::avg('rating') ?? 0, 2),
                'total' => Rating::count(),
                'distribution' => Rating::select('rating', DB::raw('count(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get()
                    ->pluck('count', 'rating'),
            ]
        ];
    }
}