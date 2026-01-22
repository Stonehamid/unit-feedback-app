<?php

namespace App\Services\Export;

use App\Models\Rating;
use App\Models\Report;
use App\Models\Unit;
use App\Models\UnitVisit;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfExportService
{
    public function __construct(
        protected PDF $pdf
    ) {}

    public function exportRatings(array $filters = []): string
    {
        $query = Rating::with(['unit', 'scores.category']);
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $ratings = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'title' => 'Laporan Rating',
            'period' => $this->getPeriodText($filters),
            'total' => $ratings->count(),
            'average' => $ratings->avg('rata_rata') ?? 0,
            'ratings' => $ratings,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = $this->pdf->loadView('exports.pdf.ratings', $data);
        
        $filename = 'ratings-report-' . date('Y-m-d-His') . '.pdf';
        $path = Storage::path('exports/' . $filename);
        
        $pdf->save($path);
        
        return $path;
    }
    
    public function exportUnits(array $filters = []): string
    {
        $query = Unit::withCount(['ratings', 'visits', 'employees']);
        
        if (isset($filters['jenis'])) {
            $query->where('jenis_unit', $filters['jenis']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status_aktif', $filters['status'] === 'aktif');
        }
        
        $units = $query->orderBy('nama_unit')->get();
        
        $data = [
            'title' => 'Laporan Unit',
            'total' => $units->count(),
            'active' => $units->where('status_aktif', true)->count(),
            'units' => $units,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = $this->pdf->loadView('exports.pdf.units', $data);
        
        $filename = 'units-report-' . date('Y-m-d-His') . '.pdf';
        $path = Storage::path('exports/' . $filename);
        
        $pdf->save($path);
        
        return $path;
    }
    
    public function exportVisits(array $filters = []): string
    {
        $query = UnitVisit::with('unit');
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('tanggal', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('tanggal', '<=', $filters['date_to']);
        }
        
        $visits = $query->orderBy('tanggal', 'desc')->orderBy('waktu_masuk', 'desc')->get();
        
        $data = [
            'title' => 'Laporan Kunjungan',
            'period' => $this->getPeriodText($filters),
            'total' => $visits->count(),
            'total_duration' => $visits->sum('durasi_detik'),
            'visits' => $visits,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = $this->pdf->loadView('exports.pdf.visits', $data);
        
        $filename = 'visits-report-' . date('Y-m-d-His') . '.pdf';
        $path = Storage::path('exports/' . $filename);
        
        $pdf->save($path);
        
        return $path;
    }
    
    public function exportReports(array $filters = []): string
    {
        $query = Report::with(['unit', 'admin']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['priority'])) {
            $query->where('prioritas', $filters['priority']);
        }
        
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        
        $reports = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'title' => 'Laporan Masukan',
            'period' => $this->getPeriodText($filters),
            'total' => $reports->count(),
            'new' => $reports->where('status', 'baru')->count(),
            'resolved' => $reports->where('status', 'selesai')->count(),
            'reports' => $reports,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
        
        $pdf = $this->pdf->loadView('exports.pdf.reports', $data);
        
        $filename = 'reports-report-' . date('Y-m-d-His') . '.pdf';
        $path = Storage::path('exports/' . $filename);
        
        $pdf->save($path);
        
        return $path;
    }
    
    public function exportDashboardStats(array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->subMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now();
        
        $stats = [
            'visits' => UnitVisit::whereBetween('tanggal', [$dateFrom, $dateTo])->count(),
            'ratings' => Rating::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'reports' => Report::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'units' => Unit::count(),
            'period' => [
                'from' => Carbon::parse($dateFrom)->format('d/m/Y'),
                'to' => Carbon::parse($dateTo)->format('d/m/Y'),
            ],
        ];
        
        return $stats;
    }
    
    private function getPeriodText(array $filters): string
    {
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $from = Carbon::parse($filters['date_from'])->format('d/m/Y');
            $to = Carbon::parse($filters['date_to'])->format('d/m/Y');
            return "Periode: $from - $to";
        }
        
        return 'Semua Data';
    }
}