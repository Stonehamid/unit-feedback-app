<?php

namespace App\Services\Export;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReportGeneratorService
{
    public function __construct(
        protected PdfExportService $pdfService,
        protected ExcelExportService $excelService
    ) {}
    
    public function generateDailyReport(): array
    {
        $date = Carbon::yesterday()->format('Y-m-d');
        
        $pdfPath = $this->pdfService->exportRatings([
            'date_from' => $date,
            'date_to' => $date,
        ]);
        
        $excelPath = $this->excelService->exportRatings([
            'date_from' => $date,
            'date_to' => $date,
        ]);
        
        return [
            'date' => $date,
            'pdf_url' => Storage::url('exports/' . basename($pdfPath)),
            'excel_url' => Storage::url('exports/' . basename($excelPath)),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
    
    public function generateWeeklyReport(): array
    {
        $weekStart = Carbon::now()->startOfWeek()->subWeek()->format('Y-m-d');
        $weekEnd = Carbon::now()->endOfWeek()->subWeek()->format('Y-m-d');
        
        $pdfPath = $this->pdfService->exportRatings([
            'date_from' => $weekStart,
            'date_to' => $weekEnd,
        ]);
        
        $excelPath = $this->excelService->exportRatings([
            'date_from' => $weekStart,
            'date_to' => $weekEnd,
        ]);
        
        return [
            'period' => "{$weekStart} - {$weekEnd}",
            'pdf_url' => Storage::url('exports/' . basename($pdfPath)),
            'excel_url' => Storage::url('exports/' . basename($excelPath)),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
    
    public function generateMonthlyReport(): array
    {
        $monthStart = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        
        $pdfPath = $this->pdfService->exportRatings([
            'date_from' => $monthStart,
            'date_to' => $monthEnd,
        ]);
        
        $excelPath = $this->excelService->exportRatings([
            'date_from' => $monthStart,
            'date_to' => $monthEnd,
        ]);
        
        return [
            'period' => "{$monthStart} - {$monthEnd}",
            'pdf_url' => Storage::url('exports/' . basename($pdfPath)),
            'excel_url' => Storage::url('exports/' . basename($excelPath)),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
    
    public function cleanupOldExports(int $days = 7): int
    {
        $directory = Storage::path('exports');
        $files = glob($directory . '/*');
        
        $deleted = 0;
        $cutoff = Carbon::now()->subDays($days)->getTimestamp();
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}