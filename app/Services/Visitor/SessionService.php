<?php

namespace App\Services\Visitor;

use App\Models\Rating;
use App\Models\Report;
use App\Models\UnitVisit;
use App\Models\VisitorSession;
use Carbon\Carbon;

class SessionService
{
    public function initVisitorSession(string $sessionId, string $ipAddress, string $userAgent): VisitorSession
    {
        return VisitorSession::firstOrCreate(
            ['session_id' => $sessionId],
            [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'terakhir_aktivitas' => Carbon::now(),
                'metadata' => ['created_at' => now()->toDateTimeString()]
            ]
        );
    }
    
    public function updateLastActivity(string $sessionId): VisitorSession
    {
        $session = VisitorSession::where('session_id', $sessionId)->first();
        
        if ($session) {
            $session->update([
                'terakhir_aktivitas' => Carbon::now(),
                'metadata' => array_merge(
                    $session->metadata ?? [],
                    ['last_updated' => now()->toDateTimeString()]
                )
            ]);
        }
        
        return $session;
    }
    
    public function getSessionInfo(string $sessionId): array
    {
        $session = VisitorSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return [
                'exists' => false,
                'session_id' => $sessionId,
                'created_at' => null
            ];
        }
        
        return [
            'exists' => true,
            'session_id' => $session->session_id,
            'created_at' => $session->created_at,
            'terakhir_aktivitas' => $session->terakhir_aktivitas,
            'is_active' => $session->isActive(),
            'ip_address' => $session->ip_address,
            'total_ratings' => $session->ratings()->count(),
            'total_reports' => $session->reports()->count(),
            'total_visits' => $session->visits()->count()
        ];
    }
    
    public function getSessionActivities(string $sessionId): array
    {
        $session = VisitorSession::where('session_id', $sessionId)->first();
        
        if (!$session) {
            return [
                'ratings' => [],
                'reports' => [],
                'visits' => [],
                'ratings_count' => 0,
                'reports_count' => 0,
                'visits_count' => 0
            ];
        }
        
        return [
            'ratings' => $session->ratings()->with('unit')->orderBy('created_at', 'desc')->limit(5)->get(),
            'reports' => $session->reports()->with('unit')->orderBy('created_at', 'desc')->limit(5)->get(),
            'visits' => $session->visits()->with('unit')->orderBy('waktu_masuk', 'desc')->limit(5)->get(),
            'ratings_count' => $session->ratings()->count(),
            'reports_count' => $session->reports()->count(),
            'visits_count' => $session->visits()->count()
        ];
    }
    
    public function canRateUnit(string $unitId, string $sessionId): bool
    {
        return !Rating::where('unit_id', $unitId)
            ->where('session_id', $sessionId)
            ->whereDate('created_at', Carbon::today())
            ->exists();
    }
}