<?php

namespace App\Services\Export;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class CsvExportService
{
    public function exportToCsv(Collection $data, array $headers, string $filename): string
    {
        $csv = $this->arrayToCsv($data->toArray(), $headers);
        
        $path = 'exports/' . $filename . '_' . date('Y-m-d_His') . '.csv';
        Storage::disk('local')->put($path, $csv);
        
        return $path;
    }
    
    public function generateCsvContent(Collection $data, array $headers): string
    {
        return $this->arrayToCsv($data->toArray(), $headers);
    }
    
    public function exportUnits(Collection $units): string
    {
        $headers = ['ID', 'Name', 'Type', 'Officer', 'Location', 'Avg Rating', 'Total Ratings', 'Total Messages', 'Status', 'Created At'];
        
        $data = $units->map(function ($unit) {
            return [
                $unit->id,
                $this->escapeCsv($unit->name),
                $this->escapeCsv($unit->type),
                $this->escapeCsv($unit->officer_name),
                $this->escapeCsv($unit->location),
                $unit->ratings_avg_rating ?? 0,
                $unit->ratings_count ?? 0,
                $unit->messages_count ?? 0,
                $unit->is_active ? 'Active' : 'Inactive',
                $unit->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        return $this->exportToCsv($data, $headers, 'units_export');
    }
    
    public function exportRatings(Collection $ratings): string
    {
        $headers = ['ID', 'Unit', 'Rating', 'Reviewer', 'Comment', 'Status', 'Created At'];
        
        $data = $ratings->map(function ($rating) {
            return [
                $rating->id,
                $this->escapeCsv($rating->unit->name ?? 'N/A'),
                $rating->rating,
                $this->escapeCsv($rating->reviewer_name),
                $this->escapeCsv($rating->comment),
                $rating->is_approved ? 'Approved' : ($rating->is_approved === false ? 'Rejected' : 'Pending'),
                $rating->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        return $this->exportToCsv($data, $headers, 'ratings_export');
    }
    
    public function exportUsers(Collection $users): string
    {
        $headers = ['ID', 'Name', 'Email', 'Role', 'Status', 'Ratings Count', 'Messages Count', 'Last Login', 'Created At'];
        
        $data = $users->map(function ($user) {
            return [
                $user->id,
                $this->escapeCsv($user->name),
                $user->email,
                ucfirst($user->role),
                $user->is_active ? 'Active' : 'Inactive',
                $user->ratings_count ?? 0,
                $user->messages_count ?? 0,
                $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Never',
                $user->created_at->format('Y-m-d H:i:s'),
            ];
        });
        
        return $this->exportToCsv($data, $headers, 'users_export');
    }
    
    private function arrayToCsv(array $data, array $headers): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
    
    private function escapeCsv(?string $value): string
    {
        if (is_null($value)) {
            return '';
        }
        
        // Escape double quotes by doubling them
        $value = str_replace('"', '""', $value);
        
        // Wrap in quotes if contains comma, newline, or double quote
        if (strpos($value, ',') !== false || strpos($value, "\n") !== false || strpos($value, '"') !== false) {
            $value = '"' . $value . '"';
        }
        
        return $value;
    }
    
    public function getExportTemplate(string $type): array
    {
        $templates = [
            'units' => [
                'headers' => ['Name', 'Type', 'Officer Name', 'Location', 'Description', 'Contact Email', 'Contact Phone', 'Working Hours'],
                'sample' => ['IT Department', 'Department', 'John Doe', 'Building A', 'IT Support Department', 'it@example.com', '08123456789', '08:00-17:00'],
            ],
            'users' => [
                'headers' => ['Name', 'Email', 'Password', 'Role', 'Is Active'],
                'sample' => ['Jane Smith', 'jane@example.com', 'password123', 'reviewer', '1'],
            ],
        ];
        
        return $templates[$type] ?? [];
    }
}