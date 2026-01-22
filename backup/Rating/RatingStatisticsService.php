<?php

namespace App\Services\Rating;

use App\Models\Rating;

class RatingStatisticsService
{
    public function getOverallStats(): array
    {
        return [
            'total' => Rating::count(),
            'with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),
            'average_rating' => round(Rating::avg('rating') ?? 0, 2),
            'distribution' => $this->getRatingDistribution(),
        ];
    }
    
    public function getRatingDistribution()
    {
        return Rating::select('rating', \DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->pluck('count', 'rating');
    }
    
    public function getAdvancedStatistics(): array
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
        
        return [
            'daily_trend' => $dailyRatings,
            'by_unit_type' => $byUnitType,
            'top_reviewers' => $topReviewers,
            'comment_stats' => $this->getCommentStats(),
        ];
    }
    
    private function getCommentStats(): array
    {
        return [
            'with_comments' => Rating::whereNotNull('comment')->where('comment', '!=', '')->count(),
            'without_comments' => Rating::whereNull('comment')->orWhere('comment', '')->count(),
        ];
    }
}