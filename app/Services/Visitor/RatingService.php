<?php

namespace App\Services\Visitor;

use App\Models\Rating;
use App\Models\RatingCategory;
use App\Models\RatingScore;
use App\Models\Unit;
use App\Models\VisitorSession;
use Carbon\Carbon;

class RatingService
{
    public function storeRating(
        string $unitId,
        array $scores,
        ?string $comment,
        string $sessionId,
        string $ipAddress,
        string $userAgent
    ): Rating {
        $unit = Unit::findOrFail($unitId);
        
        $this->ensureVisitorSession($sessionId, $ipAddress, $userAgent);
        
        $rating = Rating::create([
            'unit_id' => $unitId,
            'session_id' => $sessionId,
            'visitor_ip' => $ipAddress,
            'user_agent' => $userAgent,
            'komentar' => $comment,
            'status' => 'pending',
            'metadata' => ['created_via' => 'visitor']
        ]);
        
        foreach ($scores as $categorySlug => $score) {
            $category = RatingCategory::where('slug', $categorySlug)
                ->where('unit_id', $unitId)
                ->first();
                
            if ($category) {
                RatingScore::create([
                    'rating_id' => $rating->id,
                    'rating_category_id' => $category->id,
                    'skor' => $score
                ]);
            }
        }
        
        return $rating->load('scores.category');
    }
    
    public function hasRecentRating(string $unitId, string $sessionId): bool
    {
        $lastRating = Rating::where('unit_id', $unitId)
            ->where('session_id', $sessionId)
            ->whereDate('created_at', Carbon::today())
            ->first();
            
        return $lastRating !== null;
    }
    
    public function getUnitRatings(string $unitId)
    {
        return Rating::where('unit_id', $unitId)
            ->with(['scores.category', 'unit'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
    
    public function getRatingCategories(string $unitId)
    {
        return RatingCategory::where('unit_id', $unitId)
            ->where('status_aktif', true)
            ->orderBy('urutan')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'nama' => $category->nama_kategori,
                    'slug' => $category->slug,
                    'deskripsi' => $category->deskripsi,
                    'wajib_diisi' => $category->wajib_diisi
                ];
            });
    }
    
    private function ensureVisitorSession(string $sessionId, string $ipAddress, string $userAgent): void
    {
        VisitorSession::updateOrCreate(
            ['session_id' => $sessionId],
            [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'terakhir_aktivitas' => Carbon::now(),
                'metadata' => ['last_action' => 'submit_rating']
            ]
        );
    }
}