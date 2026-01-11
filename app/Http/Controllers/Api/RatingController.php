<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function store(Request $request, Unit $unit)
    {
        $request->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string',
        ]);

        $rating = $unit->ratings()->create($request->validated());

        // PERUBAHAN DI SINI:
        // Lewatkan objek unit secara langsung
        $this->unitService->updateAverageRating($unit);

        return response()->json($rating, 201);
    }

    public function destroy(Rating $rating)
    {
        $this->authorize('delete', $rating->unit);

        // PERUBAHAN DI SINI:
        // Lewatkan objek unit secara langsung
        $this->unitService->updateAverageRating($rating->unit);

        $rating->delete();

        return response()->json(null, 204);
    }
}