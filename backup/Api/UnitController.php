<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Unit::withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->where('is_active', true)
                ->where('status', 'OPEN')
                ->latest();

            if ($request->search) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            if ($request->type) {
                $query->where('type', $request->type);
            }

            if ($request->featured) {
                $query->where('featured', true);
            }

            $units = $query->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'units' => $units
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch units'
            ], 500);
        }
    }

    public function adminIndex(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $query = Unit::withAvg('ratings', 'rating')
            ->withCount('ratings')
            ->latest();

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $units = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'units' => $units
            ]
        ]);
    }

    public function show(Unit $unit)
    {
        if (!$unit->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not available'
            ], 404);
        }

        $unit->loadAvg('ratings', 'rating');
        $unit->loadCount('ratings');

        return response()->json([
            'success' => true,
            'data' => [
                'unit' => $unit,
                'operating_hours' => $unit->operating_hours,
                'is_operating' => $unit->is_operating,
                'status_label' => $unit->status_label,
            ]
        ]);
    }

    public function adminShow(Unit $unit)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $unit->load(['ratings', 'messages']);
        $unit->loadAvg('ratings', 'rating');
        $unit->loadCount(['ratings', 'messages']);

        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'officer_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'location' => 'required|string|max:255',
            'status' => 'required|in:OPEN,CLOSED,FULL',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('unit-photos', 'public');
        }

        $validated['status_changed_at'] = now();
        
        $unit = Unit::create($validated);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit created successfully'
        ]);
    }

    public function update(Request $request, Unit $unit)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:units,name,' . $unit->id,
            'officer_name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:10',
            'location' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:OPEN,CLOSED,FULL',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i|after:opening_time',
            'is_active' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->has('status') && $request->status !== $unit->status) {
            $validated['status_changed_at'] = now();
        }

        if ($request->hasFile('photo')) {
            if ($unit->photo) {
                \Storage::disk('public')->delete($unit->photo);
            }
            $validated['photo'] = $request->file('photo')->store('unit-photos', 'public');
        }

        if ($request->has('remove_photo') && $request->remove_photo) {
            if ($unit->photo) {
                \Storage::disk('public')->delete($unit->photo);
            }
            $validated['photo'] = null;
        }

        $unit->update($validated);

        return response()->json([
            'success' => true,
            'data' => $unit,
            'message' => 'Unit updated successfully'
        ]);
    }

    public function destroy(Unit $unit)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($unit->photo) {
            \Storage::disk('public')->delete($unit->photo);
        }

        $unit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully'
        ]);
    }
}