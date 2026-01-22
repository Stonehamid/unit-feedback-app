<?php

namespace App\Services\Admin;

use App\Models\Rating;
use Carbon\Carbon;

class RatingService
{
    public function getRatings(array $filters = [])
    {
        $query = Rating::with(['unit', 'scores.category', 'session']);
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('komentar', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('session_id', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('visitor_ip', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';
        
        return $query->orderBy($sort, $order)->paginate($filters['per_page'] ?? 20);
    }
    
    public function getRatingDetail(string $id)
    {
        return Rating::with(['unit', 'scores.category', 'session'])
            ->findOrFail($id);
    }
    
    public function replyToRating(string $id, array $data): Rating
    {
        $rating = Rating::findOrFail($id);
        
        $rating->update([
            'status' => 'dibalas',
            'dibalas_pada' => Carbon::now(),
            'metadata' => array_merge(
                $rating->metadata ?? [],
                [
                    'admin_reply' => [
                        'message' => $data['balasan'],
                        'replied_at' => now()->toDateTimeString()
                    ]
                ]
            )
        ]);
        
        return $rating;
    }
    
    public function updateRatingStatus(string $id, string $status): Rating
    {
        $rating = Rating::findOrFail($id);
        
        $rating->update([
            'status' => $status,
            'dibalas_pada' => $status === 'dibalas' ? Carbon::now() : null
        ]);
        
        return $rating;
    }
    
    public function deleteRating(string $id): void
    {
        $rating = Rating::findOrFail($id);
        $rating->delete();
    }
    
    public function getRatingStats(): array
    {
        $total = Rating::count();
        $pending = Rating::where('status', 'pending')->count();
        $replied = Rating::where('status', 'dibalas')->count();
        $completed = Rating::where('status', 'selesai')->count();
        
        $today = Rating::whereDate('created_at', Carbon::today())->count();
        $week = Rating::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $month = Rating::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        
        $averageScore = Rating::has('scores')->with('scores')->get()->avg('rata_rata') ?? 0;
        
        return [
            'total' => $total,
            'by_status' => [
                'pending' => $pending,
                'dibalas' => $replied,
                'selesai' => $completed
            ],
            'by_period' => [
                'hari_ini' => $today,
                'minggu_ini' => $week,
                'bulan_ini' => $month
            ],
            'average_score' => round($averageScore, 1),
            'completion_rate' => $total > 0 ? round(($replied + $completed) / $total * 100, 1) : 0
        ];
    }
}