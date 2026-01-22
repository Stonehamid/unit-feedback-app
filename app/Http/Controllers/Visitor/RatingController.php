<?php

namespace App\Http\Controllers\Visitor;

use App\Http\Controllers\Controller;

use App\Http\Requests\Visitor\StoreRatingRequest;
use App\Services\Visitor\RatingService;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function __construct(
        protected RatingService $ratingService
    ) {}

    public function store(StoreRatingRequest $request, string $unitId): JsonResponse
    {
        $sessionId = $request->session()->getId();

        if ($this->ratingService->hasRecentRating($unitId, $sessionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan rating untuk unit ini hari ini. Silakan kembali besok.',
                'cooldown_until' => now()->addDay()->toDateTimeString()
            ], 429);
        }

        $rating = $this->ratingService->storeRating(
            unitId: $unitId,
            scores: $request->validated('scores'),
            comment: $request->validated('comment'),
            sessionId: $sessionId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda!',
            'data' => [
                'rating_id' => $rating->id,
                'unit_id' => $rating->unit_id,
                'can_rate_again_at' => now()->addDay()->toDateTimeString()
            ]
        ], 201);
    }

    public function checkCooldown(string $unitId): JsonResponse
    {
        $sessionId = request()->session()->getId();
        $hasRecentRating = $this->ratingService->hasRecentRating($unitId, $sessionId);

        return response()->json([
            'success' => true,
            'data' => [
                'can_rate' => !$hasRecentRating,
                'cooldown_until' => $hasRecentRating ? now()->addDay()->toDateTimeString() : null
            ]
        ]);
    }

    public function getUnitRatings(string $unitId): JsonResponse
    {
        $ratings = $this->ratingService->getUnitRatings($unitId);

        return response()->json([
            'success' => true,
            'data' => [
                'unit_id' => $unitId,
                'ratings' => $ratings,
                'average_score' => $ratings->avg('average_score'),
                'total_ratings' => $ratings->count()
            ]
        ]);
    }

    public function getRatingCategories(string $unitId): JsonResponse
    {
        $categories = $this->ratingService->getRatingCategories($unitId);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}