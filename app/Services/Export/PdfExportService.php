<?php

namespace App\Services\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PdfExportService
{
    public function exportToPdf(string $html, string $filename, array $options = []): string
    {
        $pdf = Pdf::loadHTML($html);
        
        // Set options
        if (isset($options['orientation'])) {
            $pdf->setPaper('A4', $options['orientation']);
        }
        
        if (isset($options['margin'])) {
            $pdf->setOption('margin-top', $options['margin']['top'] ?? 10);
            $pdf->setOption('margin-right', $options['margin']['right'] ?? 10);
            $pdf->setOption('margin-bottom', $options['margin']['bottom'] ?? 10);
            $pdf->setOption('margin-left', $options['margin']['left'] ?? 10);
        }
        
        $path = 'exports/' . $filename . '_' . date('Y-m-d_His') . '.pdf';
        Storage::disk('local')->put($path, $pdf->output());
        
        return $path;
    }
    
    public function generateUnitsPdf(Collection $units): string
    {
        $html = $this->generateUnitsHtml($units);
        return $this->exportToPdf($html, 'units_report', [
            'orientation' => 'landscape',
            'margin' => ['top' => 15, 'right' => 15, 'bottom' => 15, 'left' => 15],
        ]);
    }
    
    public function generateRatingsPdf(Collection $ratings): string
    {
        $html = $this->generateRatingsHtml($ratings);
        return $this->exportToPdf($html, 'ratings_report', [
            'orientation' => 'portrait',
        ]);
    }
    
    public function generateUsersPdf(Collection $users): string
    {
        $html = $this->generateUsersHtml($users);
        return $this->exportToPdf($html, 'users_report', [
            'orientation' => 'portrait',
        ]);
    }
    
    public function generateStatisticsPdf(array $stats): string
    {
        $html = $this->generateStatisticsHtml($stats);
        return $this->exportToPdf($html, 'statistics_report', [
            'orientation' => 'portrait',
        ]);
    }
    
    private function generateUnitsHtml(Collection $units): string
    {
        $totalUnits = $units->count();
        $activeUnits = $units->where('is_active', true)->count();
        $avgRating = round($units->avg('ratings_avg_rating') ?? 0, 2);
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Units Report</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #3b82f6; margin-bottom: 5px; }
                .header .subtitle { color: #6b7280; font-size: 14px; }
                .summary { background: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .summary-item { display: inline-block; margin-right: 30px; }
                .summary-label { font-weight: bold; color: #4b5563; }
                .summary-value { color: #111827; font-size: 18px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #3b82f6; color: white; padding: 10px; text-align: left; }
                td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
                tr:nth-child(even) { background: #f9fafb; }
                .rating { color: #f59e0b; font-weight: bold; }
                .status-active { color: #10b981; font-weight: bold; }
                .status-inactive { color: #ef4444; font-weight: bold; }
                .footer { margin-top: 40px; text-align: center; color: #6b7280; font-size: 11px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Units Report</h1>
                <div class="subtitle">Generated on ' . date('F j, Y H:i:s') . '</div>
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <div class="summary-label">Total Units</div>
                    <div class="summary-value">' . $totalUnits . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Active Units</div>
                    <div class="summary-value">' . $activeUnits . '</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Average Rating</div>
                    <div class="summary-value">' . $avgRating . ' ⭐</div>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Officer</th>
                        <th>Location</th>
                        <th>Avg Rating</th>
                        <th>Total Ratings</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($units as $unit) {
            $statusClass = $unit->is_active ? 'status-active' : 'status-inactive';
            $statusText = $unit->is_active ? 'Active' : 'Inactive';
            
            $html .= '
                    <tr>
                        <td>' . $unit->id . '</td>
                        <td>' . htmlspecialchars($unit->name) . '</td>
                        <td>' . htmlspecialchars($unit->type) . '</td>
                        <td>' . htmlspecialchars($unit->officer_name) . '</td>
                        <td>' . htmlspecialchars($unit->location) . '</td>
                        <td class="rating">' . ($unit->ratings_avg_rating ?? '0.00') . ' ⭐</td>
                        <td>' . ($unit->ratings_count ?? 0) . '</td>
                        <td class="' . $statusClass . '">' . $statusText . '</td>
                        <td>' . $unit->created_at->format('Y-m-d') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
            
            <div class="footer">
                Page 1 of 1 | Unit Rating System © ' . date('Y') . '
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function generateRatingsHtml(Collection $ratings): string
    {
        $totalRatings = $ratings->count();
        $avgRating = round($ratings->avg('rating') ?? 0, 2);
        $approvedRatings = $ratings->where('is_approved', true)->count();
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ratings Report</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { color: #3b82f6; margin-bottom: 5px; }
                .summary { background: #f3f4f6; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .summary-item { display: inline-block; margin-right: 30px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th { background: #8b5cf6; color: white; padding: 10px; text-align: left; }
                td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
                .star-rating { color: #f59e0b; }
                .comment { max-width: 300px; overflow: hidden; text-overflow: ellipsis; }
                .status-approved { color: #10b981; }
                .status-rejected { color: #ef4444; }
                .status-pending { color: #f59e0b; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Ratings Report</h1>
                <div>Generated on ' . date('F j, Y H:i:s') . '</div>
            </div>
            
            <div class="summary">
                <div class="summary-item">
                    <strong>Total Ratings:</strong> ' . $totalRatings . '
                </div>
                <div class="summary-item">
                    <strong>Average Rating:</strong> ' . $avgRating . ' ⭐
                </div>
                <div class="summary-item">
                    <strong>Approved Ratings:</strong> ' . $approvedRatings . '
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Unit</th>
                        <th>Rating</th>
                        <th>Reviewer</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($ratings as $rating) {
            $statusClass = match(true) {
                $rating->is_approved === true => 'status-approved',
                $rating->is_approved === false => 'status-rejected',
                default => 'status-pending',
            };
            
            $statusText = match(true) {
                $rating->is_approved === true => 'Approved',
                $rating->is_approved === false => 'Rejected',
                default => 'Pending',
            };
            
            $stars = str_repeat('⭐', $rating->rating);
            
            $html .= '
                    <tr>
                        <td>' . $rating->id . '</td>
                        <td>' . htmlspecialchars($rating->unit->name ?? 'N/A') . '</td>
                        <td class="star-rating">' . $stars . ' (' . $rating->rating . ')</td>
                        <td>' . htmlspecialchars($rating->reviewer_name) . '</td>
                        <td class="comment">' . htmlspecialchars(substr($rating->comment ?? '', 0, 100)) . '</td>
                        <td class="' . $statusClass . '">' . $statusText . '</td>
                        <td>' . $rating->created_at->format('Y-m-d H:i') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
        
        return $html;
    }
    
    private function generateUsersHtml(Collection $users): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Users Report</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th { background: #10b981; color: white; padding: 10px; }
                td { padding: 8px; border-bottom: 1px solid #ddd; }
                .role-admin { color: #ef4444; font-weight: bold; }
                .role-reviewer { color: #3b82f6; }
                .role-user { color: #6b7280; }
            </style>
        </head>
        <body>
            <h1>Users Report - ' . date('Y-m-d') . '</h1>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($users as $user) {
            $roleClass = 'role-' . $user->role;
            $status = $user->is_active ? 'Active' : 'Inactive';
            
            $html .= '
                    <tr>
                        <td>' . $user->id . '</td>
                        <td>' . htmlspecialchars($user->name) . '</td>
                        <td>' . $user->email . '</td>
                        <td class="' . $roleClass . '">' . ucfirst($user->role) . '</td>
                        <td>' . $status . '</td>
                        <td>' . $user->created_at->format('Y-m-d') . '</td>
                    </tr>';
        }
        
        $html .= '
                </tbody>
            </table>
        </body>
        </html>';
        
        return $html;
    }
    
    private function generateStatisticsHtml(array $stats): string
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>System Statistics</title>
            <style>
                body { font-family: DejaVu Sans, sans-serif; }
                .stat-card { 
                    border: 1px solid #e5e7eb; 
                    border-radius: 8px; 
                    padding: 20px; 
                    margin: 10px 0; 
                    background: #f9fafb;
                }
                .stat-title { color: #4b5563; font-size: 14px; }
                .stat-value { color: #111827; font-size: 24px; font-weight: bold; }
                .chart-placeholder { 
                    background: #f3f4f6; 
                    height: 200px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center;
                    color: #6b7280;
                    margin: 20px 0;
                    border-radius: 8px;
                }
            </style>
        </head>
        <body>
            <h1>System Statistics Report</h1>
            <p>Generated on: ' . date('F j, Y, g:i a') . '</p>';
        
        foreach ($stats as $category => $data) {
            $html .= '<h2>' . ucfirst(str_replace('_', ' ', $category)) . '</h2>';
            
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $html .= '
                    <div class="stat-card">
                        <div class="stat-title">' . ucfirst(str_replace('_', ' ', $key)) . '</div>
                        <div class="stat-value">' . $value . '</div>
                    </div>';
                }
            } else {
                $html .= '<p>' . $data . '</p>';
            }
        }
        
        $html .= '
            <div class="chart-placeholder">
                Statistics Visualization
            </div>
            
            <p style="margin-top: 30px; color: #6b7280; font-size: 12px;">
                This report was automatically generated by Unit Rating System.
            </p>
        </body>
        </html>';
        
        return $html;
    }
    
    public function generateDownloadResponse(string $filePath, string $filename = null)
    {
        if (!Storage::disk('local')->exists($filePath)) {
            throw new \Exception('PDF file not found');
        }
        
        $filename = $filename ?? basename($filePath);
        
        return response()->download(
            storage_path('app/' . $filePath),
            $filename,
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }
}