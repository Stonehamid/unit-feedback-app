<?php

namespace App\Services\Report;

use App\Models\Report;
use Illuminate\Support\Facades\Storage;

class ReportExportService
{
    public function exportToCsv(array $reportIds = null)
    {
        $query = Report::with(['admin:id,name', 'unit:id,name']);
        
        if ($reportIds) {
            $query->whereIn('id', $reportIds);
        }
        
        $reports = $query->get();
        
        $csv = "ID,Title,Type,Priority,Status,Unit,Admin,Created At,Updated At\n";
        
        foreach ($reports as $report) {
            $csv .= implode(',', [
                $report->id,
                '"' . str_replace('"', '""', $report->title) . '"',
                '"' . str_replace('"', '""', $report->type ?? 'N/A') . '"',
                '"' . str_replace('"', '""', $report->priority ?? 'N/A') . '"',
                '"' . str_replace('"', '""', $report->status ?? 'draft') . '"',
                '"' . str_replace('"', '""', $report->unit->name ?? 'N/A') . '"',
                '"' . str_replace('"', '""', $report->admin->name ?? 'N/A') . '"',
                $report->created_at->format('Y-m-d H:i:s'),
                $report->updated_at->format('Y-m-d H:i:s'),
            ]) . "\n";
        }
        
        return $this->createDownloadResponse($csv, 'reports-export-' . date('Y-m-d') . '.csv');
    }
    
    public function exportToJson(array $reportIds = null)
    {
        $query = Report::with(['admin:id,name,email', 'unit:id,name,type']);
        
        if ($reportIds) {
            $query->whereIn('id', $reportIds);
        }
        
        $reports = $query->get()->map(function($report) {
            return [
                'id' => $report->id,
                'title' => $report->title,
                'type' => $report->type,
                'priority' => $report->priority,
                'status' => $report->status,
                'content_preview' => substr($report->content, 0, 100) . (strlen($report->content) > 100 ? '...' : ''),
                'unit' => [
                    'id' => $report->unit->id,
                    'name' => $report->unit->name,
                    'type' => $report->unit->type,
                ],
                'admin' => [
                    'id' => $report->admin->id,
                    'name' => $report->admin->name,
                    'email' => $report->admin->email,
                ],
                'created_at' => $report->created_at->toISOString(),
                'updated_at' => $report->updated_at->toISOString(),
            ];
        });
        
        return response()->json([
            'count' => $reports->count(),
            'export_date' => now()->toISOString(),
            'reports' => $reports,
        ]);
    }
    
    public function generateStatisticsReport(): array
    {
        $totalReports = Report::count();
        $reportsThisMonth = Report::whereMonth('created_at', now()->month)->count();
        
        $byType = Report::selectRaw('type, COUNT(*) as count')
            ->whereNotNull('type')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->get()
            ->pluck('count', 'type');
        
        $byPriority = Report::selectRaw('priority, COUNT(*) as count')
            ->whereNotNull('priority')
            ->groupBy('priority')
            ->orderByRaw("FIELD(priority, 'critical', 'high', 'medium', 'low')")
            ->get()
            ->pluck('count', 'priority');
        
        $topUnits = Report::selectRaw('unit_id, COUNT(*) as count')
            ->with('unit:id,name')
            ->groupBy('unit_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'unit' => $item->unit->name,
                    'count' => $item->count,
                ];
            });
        
        return [
            'summary' => [
                'total_reports' => $totalReports,
                'reports_this_month' => $reportsThisMonth,
                'average_per_month' => round($totalReports / max(1, Report::distinct()->count(\DB::raw('MONTH(created_at)'))), 2),
            ],
            'breakdown' => [
                'by_type' => $byType,
                'by_priority' => $byPriority,
            ],
            'top_units' => $topUnits,
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