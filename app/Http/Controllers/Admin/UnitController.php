<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use App\Services\Unit\UnitService;

class UnitController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    public function index(Request $request)
    {
        $query = Unit::withCount('ratings')
            ->select(
                'id',
                'name',
                'officer_name',
                'type',
                'description',
                'location',
                'status',
                'photo',
                'avg_rating',
                'is_active',
                'featured',
                'created_at'
            )
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('officer_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $units = $query->paginate(10)->withQueryString();

        // Get unique types for filter dropdown
        $types = Unit::distinct()->pluck('type');

        return view('admin.units.index', compact('units', 'types'));
    }

    public function apiIndex(Request $request)
    {
        $units = Unit::withCount('ratings')
            ->select(
                'id',
                'name',
                'officer_name',
                'type',
                'description',
                'location',
                'status',
                'avg_rating',
                'is_active',
                'featured',
                'opening_time',
                'closing_time',
                'created_at'
            )
            ->latest()
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'units' => $units,
                'total' => Unit::count(),
            ]
        ]);
    }

    public function create()
    {
        return view('admin.units.create');
    }

    public function show($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $unit
        ]);
    }

    public function edit($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            abort(404);
        }

        return view('admin.units.edit', compact('unit'));
    }

    public function store(Request $request)
    {
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
            'is_active' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
            'photo' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('unit-photos', 'public');
        }

        $unit = Unit::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $unit,
                'message' => 'Unit created successfully'
            ]);
        }

        return redirect()->route('admin.units.view')
            ->with('success', 'Unit created successfully');
    }

    public function update(Request $request, $id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:units,name,' . $id,
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

    public function destroy($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], 404);
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

    public function statistics($id)
    {
        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        $ratings = $unit->ratings()->where('is_approved', true)->get();

        $totalRatings = $ratings->count();
        $averageRating = $totalRatings > 0 ? $ratings->avg('rating') : 0;

        $ratingDistribution = [
            5 => $ratings->where('rating', 5)->count(),
            4 => $ratings->where('rating', 4)->count(),
            3 => $ratings->where('rating', 3)->count(),
            2 => $ratings->where('rating', 2)->count(),
            1 => $ratings->where('rating', 1)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'unit_id' => $unit->id,
                'unit_name' => $unit->name,
                'status' => $unit->status,
                'status_label' => $unit->status_label,
                'total_ratings' => $totalRatings,
                'average_rating' => round($averageRating, 2),
                'rating_distribution' => $ratingDistribution,
                'messages_count' => $unit->messages()->count(),
                'reports_count' => $unit->reports()->count(),
            ]
        ]);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,id',
            'action' => 'required|in:activate,deactivate,feature,unfeature,mark_open,mark_closed,mark_full',
        ]);

        $count = 0;
        $units = Unit::whereIn('id', $request->unit_ids)->get();

        foreach ($units as $unit) {
            switch ($request->action) {
                case 'activate':
                    $unit->update(['is_active' => true]);
                    $count++;
                    break;
                case 'deactivate':
                    $unit->update(['is_active' => false]);
                    $count++;
                    break;
                case 'feature':
                    $unit->update(['featured' => true]);
                    $count++;
                    break;
                case 'unfeature':
                    $unit->update(['featured' => false]);
                    $count++;
                    break;
                case 'mark_open':
                    $unit->update(['status' => 'OPEN', 'status_changed_at' => now()]);
                    $count++;
                    break;
                case 'mark_closed':
                    $unit->update(['status' => 'CLOSED', 'status_changed_at' => now()]);
                    $count++;
                    break;
                case 'mark_full':
                    $unit->update(['status' => 'FULL', 'status_changed_at' => now()]);
                    $count++;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Action completed successfully. {$count} units updated."
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:OPEN,CLOSED,FULL',
        ]);

        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        $unit->update([
            'status' => $request->status,
            'status_changed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'unit' => $unit,
                'status_label' => $unit->status_label,
                'status_color' => $unit->status_color,
            ],
            'message' => 'Status updated successfully'
        ]);
    }
}