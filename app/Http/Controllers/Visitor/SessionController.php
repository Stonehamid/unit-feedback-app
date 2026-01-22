<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;
use App\Services\Visitor\SessionService;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
    public function __construct(
        protected SessionService $sessionService
    ) {}

    public function init(): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $session = $this->sessionService->initVisitorSession(
            sessionId: $sessionId,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->session_id,
                'created_at' => $session->created_at,
                'is_new' => $session->wasRecentlyCreated
            ]
        ], 201);
    }

    public function update(): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $session = $this->sessionService->updateLastActivity($sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $session->session_id,
                'terakhir_aktivitas' => $session->terakhir_aktivitas,
                'is_active' => $session->isActive()
            ]
        ]);
    }

    public function info(): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $info = $this->sessionService->getSessionInfo($sessionId);

        return response()->json([
            'success' => true,
            'data' => $info
        ]);
    }

    public function activities(): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $activities = $this->sessionService->getSessionActivities($sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'session_id' => $sessionId,
                'activities' => $activities,
                'total_ratings' => $activities['ratings_count'],
                'total_reports' => $activities['reports_count'],
                'total_visits' => $activities['visits_count']
            ]
        ]);
    }

    public function validateRating(string $unitId): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $canRate = $this->sessionService->canRateUnit($unitId, $sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'can_rate' => $canRate,
                'message' => $canRate 
                    ? 'Anda dapat memberikan rating untuk unit ini.' 
                    : 'Anda sudah memberikan rating untuk unit ini hari ini.'
            ]
        ]);
    }
}