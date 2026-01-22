<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Rating\ReplyRatingRequest;
use App\Models\Rating;
use App\Services\Admin\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(
        protected RatingService $ratingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $ratings = $this->ratingService->getRatings($request->all());

        return response()->json([
            'success' => true,
            'data' => $ratings,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $rating = $this->ratingService->getRatingDetail($id);

        return response()->json([
            'success' => true,
            'data' => $rating,
        ]);
    }

    public function reply(ReplyRatingRequest $request, string $id): JsonResponse
    {
        $rating = $this->ratingService->replyToRating($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dikirim',
            'data' => $rating,
        ]);
    }

    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dibalas,selesai',
        ]);

        $rating = $this->ratingService->updateRatingStatus($id, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => 'Status rating berhasil diperbarui',
            'data' => $rating,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->ratingService->deleteRating($id);

        return response()->json([
            'success' => true,
            'message' => 'Rating berhasil dihapus',
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = $this->ratingService->getRatingStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}