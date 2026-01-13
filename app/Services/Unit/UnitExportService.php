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
            'excel' => $this->exportToExcel($units),
            'pdf' => $this->exportToPdf($units),
            default => $this->exportToJson($units),
        };
    }
    
    public function exportToCsv($units)
    {
        $csv = "ID,Name,Type,Officer,Location,Avg Rating,Total Ratings,Total Messages,Status,Featured,Created At\n";
        
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
                $unit->is_active ? 'Active' : 'Inactive',
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
                'officer_name' => $unit->officer_name,
                'location' => $unit->location,
                'description' => $unit->description,
                'contact_email' => $unit->contact_email,
                'contact_phone' => $unit->contact_phone,
                'working_hours' => $unit->working_hours,
                'statistics' => [
                    'average_rating' => $unit->ratings_avg_rating ?? 0,
                    'total_ratings' => $unit->ratings_count,
                    'total_messages' => $unit->messages_count,
                ],
                'status' => [
                    'is_active' => (bool) $unit->is_active,
                    'featured' => (bool) $unit->featured,
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
    
    public function exportToExcel($units)
    {
        // For Excel export, you might use a package like Laravel Excel
        // This is a simplified version
        $csv = $this->exportToCsv($units);
        
        // Change filename to .xls
        $filename = 'units-export-' . date('Y-m-d') . '.xls';
        
        return response($csv, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
    
    public function exportToPdf($units)
    {
        // This would require a PDF library like DomPDF
        // Here's a conceptual implementation
        
        $html = '<html><head><style>
            body { font-family: Arial; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
        </style></head><body>';
        
        $html .= '<h1>Units Export - ' . date('Y-m-d') . '</h1>';
        $html .= '<table>';
        $html .= '<tr><th>ID</th><th>Name</th><th>Type</th><th>Officer</th><th>Avg Rating</th><th>Total Ratings</th></tr>';
        
        foreach ($units as $unit) {
            $html .= '<tr>';
            $html .= '<td>' . $unit->id . '</td>';
            $html .= '<td>' . htmlspecialchars($unit->name) . '</td>';
            $html .= '<td>' . htmlspecialchars($unit->type) . '</td>';
            $html .= '<td>' . htmlspecialchars($unit->officer_name) . '</td>';
            $html .= '<td>' . ($unit->ratings_avg_rating ?? 0) . '</td>';
            $html .= '<td>' . $unit->ratings_count . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '<p>Total Units: ' . $units->count() . '</p>';
        $html .= '</body></html>';
        
        // In real implementation, you would use:
        // $pdf = \PDF::loadHTML($html);
        // return $pdf->download('units-export-' . date('Y-m-d') . '.pdf');
        
        return response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="units-export-' . date('Y-m-d') . '.pdf"',
        ]);
    }
    
    public function generateStatisticsReport(): array
    {
        $totalUnits = Unit::count();
        $activeUnits = Unit::where('is_active', true)->count();
        $featuredUnits = Unit::where('featured', true)->count();
        
        $byType = Unit::selectRaw('type, COUNT(*) as count, AVG(avg_rating) as avg_rating')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'type' => $item->type,
                    'count' => $item->count,
                    'average_rating' => round($item->avg_rating, 2),
                ];
            });
        
        $topRatedUnits = Unit::orderBy('avg_rating', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'type', 'avg_rating', 'ratings_count']);
        
        $mostActiveUnits = Unit::withCount('ratings')
            ->orderBy('ratings_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'type', 'ratings_count']);
        
        return [
            'summary' => [
                'total_units' => $totalUnits,
                'active_units' => $activeUnits,
                'featured_units' => $featuredUnits,
                'inactive_units' => $totalUnits - $activeUnits,
            ],
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