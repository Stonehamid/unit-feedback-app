<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\Rating;
use App\Models\Message;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UnitController extends Controller
{
    private function logAdminAction($action, $data = [])
    {
        Log::channel('admin')->info('Admin Action: ' . $action, array_merge([
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name,
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
        ], $data));
    }

    public function index(Request $request)
    {
        $query = Unit::query();

        $query->withCount(['ratings', 'messages', 'reports'])
            ->withAvg('ratings', 'rating');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('min_rating')) {
            $query->having('ratings_avg_rating', '>=', $request->min_rating);
        }

        if ($request->has('max_rating')) {
            $query->having('ratings_avg_rating', '<=', $request->max_rating);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        if ($request->has('officer')) {
            $query->where('officer_name', 'like', "%{$request->officer}%");
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('officer_name', 'like', "%{$search}%");
            });
        }

        if ($request->has('activity')) {
            if ($request->activity === 'active') {
                $query->has('ratings', '>', 0);
            } elseif ($request->activity === 'inactive') {
                $query->doesntHave('ratings');
            }
        }

        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');

        if ($orderBy === 'avg_rating') {
            $query->orderBy('avg_rating', $orderDir);
        } elseif ($orderBy === 'ratings_count') {
            $query->orderBy('ratings_count', $orderDir);
        } elseif ($orderBy === 'messages_count') {
            $query->orderBy('messages_count', $orderDir);
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        $perPage = $request->get('per_page', 20);
        $units = $query->paginate($perPage);

        $types = Unit::distinct()->pluck('type');

        $stats = [
            'total_units' => Unit::count(),
            'avg_rating_all' => round(Unit::avg('avg_rating') ?? 0, 2),
            'total_ratings' => Rating::count(),
            'total_messages' => Message::count(),
            'types_distribution' => Unit::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
        ];

        return [
            'units' => $units,
            'filters' => [
                'types' => $types,
                'applied' => $request->all(),
            ],
            'stats' => $stats,
        ];
    }

    public function show(Unit $unit)
    {
        $unit->load([
            'ratings' => function ($query) {
                $query->latest()->limit(20);
            },
            'messages' => function ($query) {
                $query->latest()->limit(20);
            },
            'reports' => function ($query) {
                $query->with('admin:id,name')->latest()->limit(10);
            }
        ]);

        $unit->loadCount(['ratings', 'messages', 'reports']);
        $unit->loadAvg('ratings', 'rating');

        $ratingDistribution = Rating::where('unit_id', $unit->id)
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->pluck('count', 'rating');

        $recentRatings = $unit->ratings()->latest()->limit(5)->get();
        $recentMessages = $unit->messages()->latest()->limit(5)->get();
        $recentReports = $unit->reports()->latest()->limit(5)->get();

        $topReviewers = Rating::where('unit_id', $unit->id)
            ->select('reviewer_name', DB::raw('count(*) as count, AVG(rating) as avg_rating'))
            ->groupBy('reviewer_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return [
            'unit' => $unit,
            'statistics' => [
                'rating_distribution' => $ratingDistribution,
                'top_reviewers' => $topReviewers,
                'average_by_month' => $this->getMonthlyAverage($unit->id),
            ],
            'recent_activity' => [
                'ratings' => $recentRatings,
                'messages' => $recentMessages,
                'reports' => $recentReports,
            ],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'officer_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'location' => 'required|string|max:255',
            'photo' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'working_hours' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('unit-photos', 'public');
        }

        $unit = Unit::create($validated);

        $this->logAdminAction('created unit', [
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
        ]);

        return [
            'unit' => $unit,
            'message' => 'Unit created successfully'
        ];
    }

    public function update(Request $request, Unit $unit)
    {
        // FIX: Pisah validation data dari update data
        $validationRules = [
            'name' => 'sometimes|string|max:255|unique:units,name,' . $unit->id,
            'officer_name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:10',
            'location' => 'sometimes|string|max:255',
            'photo' => 'nullable|image|max:5120|mimes:jpg,jpeg,png,webp',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'working_hours' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
            'featured' => 'sometimes|boolean',
        ];

        $validated = $request->validate($validationRules);

        // Data untuk update
        $updateData = $validated;

        // Handle photo upload/delete
        if ($request->hasFile('photo')) {
            if ($unit->photo) {
                Storage::disk('public')->delete($unit->photo);
            }
            $updateData['photo'] = $request->file('photo')->store('unit-photos', 'public');
        } elseif ($request->has('remove_photo') && $request->remove_photo === true) {
            if ($unit->photo) {
                Storage::disk('public')->delete($unit->photo);
                $updateData['photo'] = null;
            }
        }

        $oldData = $unit->toArray();
        $unit->update($updateData);

        $this->logAdminAction('updated unit', [
            'unit_id' => $unit->id,
            'unit_name' => $unit->name,
            'changes' => array_keys($updateData),
        ]);

        return [
            'unit' => $unit->fresh(),
            'message' => 'Unit updated successfully'
        ];
    }

    public function destroy(Unit $unit)
    {
        $hasRatings = $unit->ratings()->exists();
        $hasMessages = $unit->messages()->exists();
        $hasReports = $unit->reports()->exists();

        if ($hasRatings || $hasMessages || $hasReports) {
            $action = request('action', 'reject');

            if ($action === 'force') {
                $unit->ratings()->delete();
                $unit->messages()->delete();
                $unit->reports()->delete();

                if ($unit->photo) {
                    Storage::disk('public')->delete($unit->photo);
                }

                $unit->delete();

                $this->logAdminAction('force deleted unit with relations', [
                    'unit_id' => $unit->id,
                    'unit_name' => $unit->name,
                    'deleted_ratings' => $hasRatings,
                    'deleted_messages' => $hasMessages,
                    'deleted_reports' => $hasReports,
                ]);

                return [
                    'message' => 'Unit and all related data deleted successfully'
                ];
            } else {
                return response()->json([
                    'message' => 'Unit cannot be deleted because it has related data.',
                    'has_ratings' => $hasRatings,
                    'has_messages' => $hasMessages,
                    'has_reports' => $hasReports,
                    'suggestion' => 'Use force delete action or deactivate the unit instead.'
                ], 422);
            }
        }

        if ($unit->photo) {
            Storage::disk('public')->delete($unit->photo);
        }

        $unitData = $unit->toArray();
        $unit->delete();

        $this->logAdminAction('deleted unit', [
            'unit_id' => $unitData['id'],
            'unit_name' => $unitData['name'],
        ]);

        return [
            'message' => 'Unit deleted successfully'
        ];
    }

    public function statistics(Unit $unit)
    {
        $ratingsOverTime = Rating::where('unit_id', $unit->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(rating) as avg_rating, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $messagesOverTime = Message::where('unit_id', $unit->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $byDayOfWeek = Rating::where('unit_id', $unit->id)
            ->selectRaw('DAYNAME(created_at) as day, COUNT(*) as count, AVG(rating) as avg_rating')
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();

        $similarUnits = Unit::where('type', $unit->type)
            ->where('id', '!=', $unit->id)
            ->select('id', 'name', 'avg_rating')
            ->withCount('ratings')
            ->orderBy('avg_rating', 'desc')
            ->limit(5)
            ->get();

        return [
            'ratings_over_time' => $ratingsOverTime,
            'messages_over_time' => $messagesOverTime,
            'by_day_of_week' => $byDayOfWeek,
            'similar_units_comparison' => $similarUnits,
            'rank' => [
                'by_rating' => Unit::where('avg_rating', '>', $unit->avg_rating)->count() + 1,
                'by_ratings_count' => Unit::whereHas('ratings', function ($q) use ($unit) {
                    $q->havingRaw('COUNT(*) > ?', [$unit->ratings()->count()]);
                })->count() + 1,
                'total_units' => Unit::count(),
            ]
        ];
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'unit_ids' => 'required|array',
            'unit_ids.*' => 'exists:units,id',
            'action' => 'required|in:activate,deactivate,feature,unfeature,export',
        ]);

        $units = Unit::whereIn('id', $request->unit_ids)->get();
        $count = 0;

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
            }
        }

        $this->logAdminAction('bulk action on units', [
            'action' => $request->action,
            'unit_count' => $count,
            'unit_ids' => $request->unit_ids,
        ]);

        return [
            'message' => "Bulk action completed. {$count} units affected."
        ];
    }

    public function export(Request $request)
    {
        $units = Unit::withCount(['ratings', 'messages'])
            ->withAvg('ratings', 'rating')
            ->get();

        if ($request->get('format') === 'csv' || $request->input('format') === 'csv') {
            $csv = "ID,Name,Type,Officer,Location,Avg Rating,Total Ratings,Total Messages,Created At\n";

            foreach ($units as $unit) {
                $csv .= implode(',', [
                    $unit->id,
                    '"' . str_replace('"', '""', $unit->name) . '"',
                    '"' . str_replace('"', '""', $unit->type) . '"',
                    '"' . str_replace('"', '""', $unit->officer_name) . '"',
                    '"' . str_replace('"', '""', $unit->location) . '"',
                    $unit->ratings_avg_rating ?? 0,
                    $unit->ratings_count,
                    $unit->messages_count,
                    $unit->created_at->format('Y-m-d')
                ]) . "\n";
            }

            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="units-export-' . date('Y-m-d') . '.csv"',
            ]);
        }

        return $units;
    }

    private function getMonthlyAverage($unitId)
    {
        return Rating::where('unit_id', $unitId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(rating) as avg_rating')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();
    }
}