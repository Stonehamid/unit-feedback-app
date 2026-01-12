<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    /**
     * Store new rating (reviewer only)
     */
    public function store(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'reviewer_name' => 'sometimes|string|max:255',
        ]);

        // Auto fill jika tidak dikirim
        if (!isset($validated['reviewer_name']) && Auth::check()) {
            $validated['reviewer_name'] = Auth::user()->name;
        } elseif (!isset($validated['reviewer_name'])) {
            $validated['reviewer_name'] = 'Anonymous';
        }

        $rating = $unit->ratings()->create($validated);

        // Update unit average rating menggunakan method di model
        $unit->updateAverageRating();

        return [
            'rating' => $rating,
            'message' => 'Rating submitted successfully'
        ];
    }

    /**
     * Delete rating (admin only)
     */
    public function destroy(Rating $rating)
    {
        $unit = $rating->unit;
        
        $rating->delete();

        // Update unit average rating after deletion
        $unit->updateAverageRating();

        return [
            'message' => 'Rating deleted successfully'
        ];
    }

    /**
     * Get my ratings (reviewer/admin)
     */
    public function myRatings()
    {
        $user = Auth::user();
        
        // Jika user adalah reviewer/admin, ambil rating berdasarkan nama
        $ratings = Rating::where('reviewer_name', $user->name)
                        ->with('unit:id,name,type')
                        ->latest()
                        ->paginate(10);

        return $ratings;
    }
}