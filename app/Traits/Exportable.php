<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

trait Exportable
{
    public function exportToCsv(array $data, string $filename, array $headers = [])
    {
        $filePath = 'exports/' . $filename . '_' . date('Y-m-d_His') . '.csv';
        
        $handle = fopen(storage_path('app/' . $filePath), 'w');
        
        if (!empty($headers)) {
            fputcsv($handle, $headers);
        }
        
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        
        return $filePath;
    }

    public function exportToJson(array $data, string $filename)
    {
        $filePath = 'exports/' . $filename . '_' . date('Y-m-d_His') . '.json';
        
        Storage::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
        
        return $filePath;
    }

    public function formatForExport(array $items, array $mapping)
    {
        $formatted = [];
        
        foreach ($items as $item) {
            $row = [];
            foreach ($mapping as $exportKey => $modelKey) {
                if (is_callable($modelKey)) {
                    $row[$exportKey] = $modelKey($item);
                } else {
                    $row[$exportKey] = data_get($item, $modelKey, '');
                }
            }
            $formatted[] = $row;
        }
        
        return $formatted;
    }

    public function getExportHeaders(array $mapping)
    {
        return array_keys($mapping);
    }

    public function generateExportFilename(string $prefix, string $type = 'csv')
    {
        $timestamp = Carbon::now()->format('Y-m-d_His');
        return "{$prefix}_{$timestamp}.{$type}";
    }

    public function cleanupOldExports(string $directory = 'exports', int $days = 7)
    {
        $files = Storage::files($directory);
        $cutoff = Carbon::now()->subDays($days)->timestamp;
        
        $deleted = 0;
        foreach ($files as $file) {
            if (Storage::lastModified($file) < $cutoff) {
                Storage::delete($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
}