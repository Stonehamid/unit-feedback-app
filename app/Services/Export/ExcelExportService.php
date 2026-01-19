<?php

namespace App\Services\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ExcelExportService
{
    public function exportToExcel(array $data, array $headers, string $filename, string $sheetTitle = 'Sheet1'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetTitle);
        
        // Set headers with styling
        $this->setHeaders($sheet, $headers);
        
        // Add data rows
        $this->addDataRows($sheet, $data, count($headers));
        
        // Auto-size columns
        $this->autoSizeColumns($sheet, count($headers));
        
        // Save file
        $path = 'exports/' . $filename . '_' . date('Y-m-d_His') . '.xlsx';
        $fullPath = storage_path('app/' . $path);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);
        
        return $path;
    }
    
    public function exportUnitsToExcel(Collection $units): string
    {
        $headers = ['ID', 'Name', 'Type', 'Officer Name', 'Location', 'Avg Rating', 'Total Ratings', 'Total Messages', 'Status', 'Created At', 'Updated At'];
        
        $data = $units->map(function ($unit) {
            return [
                $unit->id,
                $unit->name,
                $unit->type,
                $unit->officer_name,
                $unit->location,
                $unit->ratings_avg_rating ?? 0,
                $unit->ratings_count ?? 0,
                $unit->messages_count ?? 0,
                $unit->is_active ? 'Active' : 'Inactive',
                $unit->created_at->format('Y-m-d H:i:s'),
                $unit->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        return $this->exportToExcel($data, $headers, 'units_export', 'Units');
    }
    
    public function exportRatingsToExcel(Collection $ratings): string
    {
        $headers = ['ID', 'Unit Name', 'Rating', 'Reviewer Name', 'Comment', 'Is Approved', 'Approved At', 'Created At'];
        
        $data = $ratings->map(function ($rating) {
            return [
                $rating->id,
                $rating->unit->name ?? 'N/A',
                $rating->rating,
                $rating->reviewer_name,
                $rating->comment ?? '',
                $rating->is_approved ? 'Yes' : ($rating->is_approved === false ? 'No' : 'Pending'),
                $rating->approved_at ? $rating->approved_at->format('Y-m-d H:i:s') : '',
                $rating->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        return $this->exportToExcel($data, $headers, 'ratings_export', 'Ratings');
    }
    
    public function exportUsersToExcel(Collection $users): string
    {
        $headers = ['ID', 'Name', 'Email', 'Role', 'Is Active', 'Last Login', 'Created At', 'Updated At'];
        
        $data = $users->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                ucfirst($user->role),
                $user->is_active ? 'Yes' : 'No',
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                $user->created_at->format('Y-m-d H:i:s'),
                $user->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
        
        return $this->exportToExcel($data, $headers, 'users_export', 'Users');
    }
    
    public function exportStatisticsToExcel(array $statistics): string
    {
        $spreadsheet = new Spreadsheet();
        
        // Dashboard Statistics Sheet
        $dashboardSheet = $spreadsheet->getActiveSheet();
        $dashboardSheet->setTitle('Dashboard Stats');
        
        $this->addStatisticsSheet($dashboardSheet, 'Dashboard Statistics', $statistics['dashboard'] ?? []);
        
        // User Statistics Sheet
        $userSheet = $spreadsheet->createSheet();
        $userSheet->setTitle('User Stats');
        $this->addStatisticsSheet($userSheet, 'User Statistics', $statistics['users'] ?? []);
        
        // Rating Statistics Sheet
        $ratingSheet = $spreadsheet->createSheet();
        $ratingSheet->setTitle('Rating Stats');
        $this->addStatisticsSheet($ratingSheet, 'Rating Statistics', $statistics['ratings'] ?? []);
        
        // Unit Statistics Sheet
        $unitSheet = $spreadsheet->createSheet();
        $unitSheet->setTitle('Unit Stats');
        $this->addStatisticsSheet($unitSheet, 'Unit Statistics', $statistics['units'] ?? []);
        
        // Save file
        $path = 'exports/statistics_export_' . date('Y-m-d_His') . '.xlsx';
        $fullPath = storage_path('app/' . $path);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);
        
        return $path;
    }
    
    private function setHeaders($sheet, array $headers): void
    {
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            
            // Style header
            $sheet->getStyle($column . '1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3B82F6'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '1E40AF'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            
            $column++;
        }
    }
    
    private function addDataRows($sheet, array $data, int $columnCount): void
    {
        $row = 2;
        
        foreach ($data as $dataRow) {
            $column = 'A';
            
            for ($i = 0; $i < $columnCount; $i++) {
                $value = $dataRow[$i] ?? '';
                $sheet->setCellValue($column . $row, $value);
                
                // Alternate row colors
                if ($row % 2 === 0) {
                    $sheet->getStyle($column . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F9FAFB'],
                        ],
                    ]);
                }
                
                // Add borders
                $sheet->getStyle($column . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'E5E7EB'],
                        ],
                    ],
                ]);
                
                $column++;
            }
            
            $row++;
        }
    }
    
    private function autoSizeColumns($sheet, int $columnCount): void
    {
        for ($i = 0; $i < $columnCount; $i++) {
            $column = chr(65 + $i); // A, B, C, etc.
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
    
    private function addStatisticsSheet($sheet, string $title, array $statistics): void
    {
        $sheet->setCellValue('A1', $title);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->mergeCells('A1:C1');
        
        $row = 3;
        foreach ($statistics as $key => $value) {
            $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $key)));
            $sheet->setCellValue('B' . $row, is_array($value) ? json_encode($value) : $value);
            
            // Style
            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);
            
            $row++;
        }
        
        // Auto-size columns
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(40);
    }
    
    public function generateDownloadResponse(string $filePath, string $filename = null)
    {
        if (!Storage::disk('local')->exists($filePath)) {
            throw new \Exception('Excel file not found');
        }
        
        $filename = $filename ?? basename($filePath);
        
        return response()->download(
            storage_path('app/' . $filePath),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }
    
    public function createTemplate(string $type): string
    {
        $templates = [
            'units' => [
                'headers' => ['Name', 'Type', 'Officer Name', 'Location', 'Description', 'Contact Email', 'Contact Phone', 'Working Hours', 'Is Active'],
                'sample_data' => [
                    ['IT Department', 'Department', 'John Doe', 'Building A', 'IT Support Department', 'it@example.com', '08123456789', '08:00-17:00', '1'],
                ],
            ],
            'users' => [
                'headers' => ['Name', 'Email', 'Password', 'Role', 'Is Active'],
                'sample_data' => [
                    ['Jane Smith', 'jane@example.com', 'password123', 'reviewer', '1'],
                ],
            ],
            'ratings' => [
                'headers' => ['Unit ID', 'Rating (1-5)', 'Reviewer Name', 'Comment', 'Is Anonymous'],
                'sample_data' => [
                    ['1', '5', 'John Doe', 'Excellent service!', '0'],
                ],
            ],
        ];
        
        $template = $templates[$type] ?? null;
        if (!$template) {
            throw new \Exception("Template type '{$type}' not found");
        }
        
        return $this->exportToExcel(
            $template['sample_data'],
            $template['headers'],
            $type . '_template',
            'Template'
        );
    }
}