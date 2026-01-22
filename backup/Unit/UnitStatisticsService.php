<?php

namespace App\Services\Unit;

use App\Models\Unit;
use App\Models\Rating;
use App\Models\Message;

class UnitStatisticsService
{
    public function getOverallStats(): array
    {
        return [
            'total_units' => Unit::count(),
            'avg_rating_all' => round(Unit::avg('avg_rating') ?? 0, 2),
            'total_ratings' => Rating::count(),
            'total_messages' => Message::count(),
            'types_distribution' => $this->getTypesDistribution(),
        ];
    }
    
    public function getUnitStatistics(Unit $unit): array
    {
        $ratingsOverTime = $this->getRatingsOverTime($unit->id);
        $messagesOverTime = $this->getMessagesOverTime($unit->id);
        $byDayOfWeek = $this->getRatingsByDayOfWeek($unit->id);
        $similarUnits = $this->getSimilarUnits($unit);
        
        return [
            'ratings_over_time' => $ratingsOverTime,
            'messages_over_time' => $messagesOverTime,
            'by_day_of_week' => $byDayOfWeek,
            'similar_units_comparison' => $similarUnits,
            'rank' => $this->getUnitRank($unit),
        ];
    }
    
    private function getTypesDistribution()
    {
        return Unit::select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');
    }
    
    private function getRatingsOverTime(int $unitId)
    {
        return Rating::where('unit_id', $unitId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(rating) as avg_rating, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
    
    private function getMessagesOverTime(int $unitId)
    {
        return Message::where('unit_id', $unitId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
    
    private function getRatingsByDayOfWeek(int $unitId)
    {
        return Rating::where('unit_id', $unitId)
            ->selectRaw('DAYNAME(created_at) as day, COUNT(*) as count, AVG(rating) as avg_rating')
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();
    }
    
    private function getSimilarUnits(Unit $unit)
    {
        return Unit::where('type', $unit->type)
            ->where('id', '!=', $unit->id)
            ->select('id', 'name', 'avg_rating')
            ->withCount('ratings')
            ->orderBy('avg_rating', 'desc')
            ->limit(5)
            ->get();
    }
    
    private function getUnitRank(Unit $unit): array
    {
        return [
            'by_rating' => Unit::where('avg_rating', '>', $unit->avg_rating)->count() + 1,
            'by_ratings_count' => Unit::whereHas('ratings', function ($q) use ($unit) {
                $q->havingRaw('COUNT(*) > ?', [$unit->ratings()->count()]);
            })->count() + 1,
            'total_units' => Unit::count(),
        ];
    }
}