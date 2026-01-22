<?php

namespace App\Services\Export;

use App\Models\Rating;
use App\Models\Report;
use App\Models\Unit;
use App\Models\UnitVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelExportService
{
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
        
        $ratings = $query->orderBy('created_at', 'desc')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Ratings Report');
        
        $headers = [
            'ID', 'Unit', 'Tanggal', 'IP Address', 
            'Komentar', 'Status', 'Rata-rata'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $row = 2;
        foreach ($ratings as $rating) {
            $average = $rating->rata_rata ?? 0;
            
            $sheet->fromArray([
                [
                    $rating->id,
                    $rating->unit->nama_unit ?? '-',
                    $rating->created_at->format('d/m/Y H:i'),
                    $rating->visitor_ip,
                    $rating->komentar ?? '-',
                    $rating->status,
                    round($average, 1)
                ]
            ], null, "A{$row}");
            
            $row++;
        }
        
        $this->applyStyles($sheet, count($ratings));
        
        $filename = 'ratings-report-' . date('Y-m-d-His') . '.xlsx';
        $path = Storage::path('exports/' . $filename);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return $path;
    }
    
    public function exportUnits(array $filters = []): string
    {
        $query = Unit::withCount(['ratings', 'visits', 'employees']);
        
        if (isset($filters['jenis'])) {
            $query->where('jenis_unit', $filters['jenis']);
        }
        
        $units = $query->orderBy('nama_unit')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Units Report');
        
        $headers = [
            'Kode Unit', 'Nama Unit', 'Jenis', 'Lokasi', 
            'Status', 'Total Rating', 'Total Kunjungan', 'Total Pekerja',
            'Jam Operasional', 'Kapasitas'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $row = 2;
        foreach ($units as $unit) {
            $operational = '';
            if ($unit->jam_buka && $unit->jam_tutup) {
                $buka = Carbon::parse($unit->jam_buka)->format('H:i');
                $tutup = Carbon::parse($unit->jam_tutup)->format('H:i');
                $operational = "{$buka} - {$tutup}";
            }
            
            $sheet->fromArray([
                [
                    $unit->kode_unit,
                    $unit->nama_unit,
                    $unit->jenis_unit,
                    $unit->lokasi,
                    $unit->status_aktif ? 'Aktif' : 'Non-Aktif',
                    $unit->ratings_count,
                    $unit->visits_count,
                    $unit->employees_count,
                    $operational,
                    $unit->kapasitas ?? '-'
                ]
            ], null, "A{$row}");
            
            $row++;
        }
        
        $this->applyStyles($sheet, count($units));
        
        $filename = 'units-report-' . date('Y-m-d-His') . '.xlsx';
        $path = Storage::path('exports/' . $filename);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
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
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Visits Report');
        
        $headers = [
            'ID', 'Unit', 'Tanggal', 'Waktu Masuk', 'Waktu Keluar',
            'Durasi (menit)', 'Session ID'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $row = 2;
        foreach ($visits as $visit) {
            $durasi = $visit->durasi_menit ?? '-';
            
            $sheet->fromArray([
                [
                    $visit->id,
                    $visit->unit->nama_unit ?? '-',
                    $visit->tanggal->format('d/m/Y'),
                    $visit->waktu_masuk->format('H:i'),
                    $visit->waktu_keluar ? $visit->waktu_keluar->format('H:i') : '-',
                    $durasi,
                    $visit->session_id
                ]
            ], null, "A{$row}");
            
            $row++;
        }
        
        $this->applyStyles($sheet, count($visits));
        
        $filename = 'visits-report-' . date('Y-m-d-His') . '.xlsx';
        $path = Storage::path('exports/' . $filename);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return $path;
    }
    
    public function exportReports(array $filters = []): string
    {
        $query = Report::with(['unit', 'admin']);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $reports = $query->orderBy('created_at', 'desc')->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Reports Report');
        
        $headers = [
            'ID', 'Unit', 'Judul', 'Tipe', 'Prioritas', 'Status',
            'Tanggal Dibuat', 'Admin Penanggung', 'Tanggal Ditanggapi'
        ];
        
        $sheet->fromArray([$headers], null, 'A1');
        
        $row = 2;
        foreach ($reports as $report) {
            $sheet->fromArray([
                [
                    $report->id,
                    $report->unit->nama_unit ?? '-',
                    $report->judul,
                    $report->tipe,
                    $report->prioritas,
                    $report->status,
                    $report->created_at->format('d/m/Y H:i'),
                    $report->admin->nama ?? '-',
                    $report->ditanggapi_pada ? $report->ditanggapi_pada->format('d/m/Y H:i') : '-'
                ]
            ], null, "A{$row}");
            
            $row++;
        }
        
        $this->applyStyles($sheet, count($reports));
        
        $filename = 'reports-report-' . date('Y-m-d-His') . '.xlsx';
        $path = Storage::path('exports/' . $filename);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        
        return $path;
    }
    
    private function applyStyles($sheet, int $dataCount): void
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $dataCount + 1;
        
        $headerRange = "A1:{$lastColumn}1";
        $dataRange = "A1:{$lastColumn}{$lastRow}";
        
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
        
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
}