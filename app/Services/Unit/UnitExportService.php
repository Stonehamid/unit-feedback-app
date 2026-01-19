<?php

namespace App\Services\Unit;

use App\Models\Unit;
use Illuminate\Support\Facades\Storage;

class UnitExportService
{
    public function exportUnits(string $format = 'json', array $unitIds = null)
    {
        $query = Unit::withCount(['ratings', 'messages'])
            ->withAvg('ratings', 'rating');
        
        if ($unitIds) {
            $query->whereIn('id', $unitIds);
        }
        
        $units = $query->get();
        
        return match($format) {
            'csv' => $this->exportToCsv($units),
            default => $this->exportToJson($units),
        };
    }
    
    public function exportToCsv($units)
    {
        $csv = "ID,Name,Type,Status,Officer,Location,Opening Time,Closing Time,Avg Rating,Total Ratings,Total Messages,Active,Featured,Created At\n";
        
        foreach ($units as $unit) {
            $csv .= implode(',', [
                $unit->id,
                '"' . str_replace('"', '""', $unit->name) . '"',
                '"' . str_replace('"', '""', $unit->type) . '"',
                $unit->status,
                '"' . str_replace('"', '""', $unit->officer_name) . '"',
                '"' . str_replace('"', '""', $unit->location) . '"',
                $unit->opening_time,
                $unit->closing_time,
                $unit->ratings_avg_rating ?? 0,
                $unit->ratings_count,
                $unit->messages_count,
                $unit->is_active ? 'Yes' : 'No',
                $unit->featured ? 'Yes' : 'No',
                $unit->created_at->format('Y-m-d')
            ]) . "\n";
        }
        
        return $this->createDownloadResponse($csv, 'units-export-' . date('Y-m-d') . '.csv');
    }
    
    public function exportToJson($units)
    {
        $formattedUnits = $units->map(function($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'type' => $unit->type,
                'status' => $unit->status,
                'officer_name' => $unit->officer_name,
                'location' => $unit->location,
                'description' => $unit->description,
                'opening_time' => $unit->opening_time,
                'closing_time' => $unit->closing_time,
                'contact_email' => $unit->contact_email,
                'contact_phone' => $unit->contact_phone,
                'statistics' => [
                    'average_rating' => $unit->ratings_avg_rating ?? 0,
                    'total_ratings' => $unit->ratings_count,
                    'total_messages' => $unit->messages_count,
                ],
                'status_info' => [
                    'label' => $unit->status_label,
                    'is_active' => (bool) $unit->is_active,
                    'featured' => (bool) $unit->featured,
                    'last_status_change' => $unit->status_changed_at,
                ],
                'timestamps' => [
                    'created_at' => $unit->created_at->toISOString(),
                    'updated_at' => $unit->updated_at->toISOString(),
                ]
            ];
        });
        
        return response()->json([
            'count' => $formattedUnits->count(),
            'export_date' => now()->toISOString(),
            'units' => $formattedUnits,
        ]);
    }
    
    public function generateStatisticsReport(): array
    {
        $totalUnits = Unit::count();
        $activeUnits = Unit::where('is_active', true)->count();
        $featuredUnits = Unit::where('featured', true)->count();
        
        $statusDistribution = Unit::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        $byType = Unit::selectRaw('type, COUNT(*) as count, AVG(avg_rating) as avg_rating')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get();
        
        $topRatedUnits = Unit::orderBy('avg_rating', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'type', 'status', 'avg_rating', 'ratings_count']);
        
        $mostActiveUnits = Unit::withCount('ratings')
            ->orderBy('ratings_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'type', 'status', 'ratings_count']);
        
        return [
            'summary' => [
                'total_units' => $totalUnits,
                'active_units' => $activeUnits,
                'featured_units' => $featuredUnits,
                'inactive_units' => $totalUnits - $activeUnits,
            ],
            'status_distribution' => $statusDistribution,
            'breakdown' => [
                'by_type' => $byType,
            ],
            'top_performers' => [
                'top_rated' => $topRatedUnits,
                'most_active' => $mostActiveUnits,
            ],
            'generated_at' => now()->toISOString(),
        ];
    }
    
    private function createDownloadResponse(string $content, string $filename)
    {
        $tempPath = storage_path('app/temp/' . $filename);
        Storage::disk('local')->put('temp/' . $filename, $content);
        
        return response()->download($tempPath, $filename, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
}