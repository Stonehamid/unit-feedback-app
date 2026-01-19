<?php

namespace App\Services\Export;

use App\Models\Rating;
use App\Models\Unit;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ExportDataJob;
use App\Models\User;
use App\Mail\ExportReadyMail;

class ExportQueueService
{
    protected $csvService;
    protected $pdfService;
    protected $excelService;
    
    public function __construct(
        CsvExportService $csvService = null,
        PdfExportService $pdfService = null,
        ExcelExportService $excelService = null
    ) {
        $this->csvService = $csvService ?? new CsvExportService();
        $this->pdfService = $pdfService ?? new PdfExportService();
        $this->excelService = $excelService ?? new ExcelExportService();
    }
    
    public function queueExport(string $type, array $data, User $user, string $format = 'csv'): string
    {
        $jobId = uniqid('export_', true);
        
        // Dispatch job to queue
        ExportDataJob::dispatch(
            $jobId,
            $type,
            $data,
            $user->id,
            $format
        )->onQueue('exports');
        
        // Store job metadata
        $this->storeJobMetadata($jobId, [
            'type' => $type,
            'format' => $format,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'status' => 'queued',
            'queued_at' => now()->toDateTimeString(),
            'data_filters' => $data,
        ]);
        
        return $jobId;
    }
    
    public function processExport(string $jobId, string $type, array $data, int $userId, string $format): void
    {
        $user = User::find($userId);
        if (!$user) {
            $this->updateJobStatus($jobId, 'failed', 'User not found');
            return;
        }
        
        try {
            $this->updateJobStatus($jobId, 'processing');
            
            $filePath = $this->generateExportFile($type, $data, $format);
            
            // Store result
            $this->updateJobStatus($jobId, 'completed', null, $filePath);
            
            // Send notification
            $this->sendExportNotification($user, $type, $format, $filePath);
            
        } catch (\Exception $e) {
            $this->updateJobStatus($jobId, 'failed', $e->getMessage());
            throw $e;
        }
    }
    
    private function generateExportFile(string $type, array $data, string $format): string
    {
        // Based on type, fetch data and generate file
        switch ($type) {
            case 'units':
                $query = $this->buildUnitsQuery($data);
                $units = $query->get();
                
                return match($format) {
                    'csv' => $this->csvService->exportUnits($units),
                    'pdf' => $this->pdfService->generateUnitsPdf($units),
                    'excel' => $this->excelService->exportUnitsToExcel($units),
                    default => throw new \Exception("Unsupported format: {$format}"),
                };
                
            case 'ratings':
                $query = $this->buildRatingsQuery($data);
                $ratings = $query->with('unit:id,name')->get();
                
                return match($format) {
                    'csv' => $this->csvService->exportRatings($ratings),
                    'pdf' => $this->pdfService->generateRatingsPdf($ratings),
                    'excel' => $this->excelService->exportRatingsToExcel($ratings),
                    default => throw new \Exception("Unsupported format: {$format}"),
                };
                
            case 'users':
                $query = $this->buildUsersQuery($data);
                $users = $query->get();
                
                return match($format) {
                    'csv' => $this->csvService->exportUsers($users),
                    'pdf' => $this->pdfService->generateUsersPdf($users),
                    'excel' => $this->excelService->exportUsersToExcel($users),
                    default => throw new \Exception("Unsupported format: {$format}"),
                };
                
            case 'statistics':
                $stats = $this->gatherStatistics($data);
                
                return match($format) {
                    'pdf' => $this->pdfService->generateStatisticsPdf($stats),
                    'excel' => $this->excelService->exportStatisticsToExcel($stats),
                    default => throw new \Exception("Unsupported format for statistics: {$format}"),
                };
                
            default:
                throw new \Exception("Unsupported export type: {$type}");
        }
    }
    
    private function buildUnitsQuery(array $filters)
    {
        $query = Unit::query()->withCount(['ratings', 'messages']);
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        return $query;
    }
    
    private function buildRatingsQuery(array $filters)
    {
        $query = Rating::query();
        
        if (isset($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }
        
        if (isset($filters['is_approved'])) {
            $query->where('is_approved', $filters['is_approved']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        return $query;
    }
    
    private function buildUsersQuery(array $filters)
    {
        $query = User::query();
        
        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        return $query;
    }
    
    private function gatherStatistics(array $filters): array
    {
        // You would implement this based on your statistics needs
        return [
            'dashboard' => [
                'total_units' => Unit::count(),
                'total_ratings' => Rating::count(),
                'total_users' => User::count(),
            ],
            'users' => [
                'by_role' => User::select('role', \DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get()
                    ->pluck('count', 'role'),
            ],
            // Add more statistics as needed
        ];
    }
    
    private function storeJobMetadata(string $jobId, array $metadata): void
    {
        $path = "exports/jobs/{$jobId}.json";
        Storage::disk('local')->put($path, json_encode($metadata, JSON_PRETTY_PRINT));
    }
    
    private function updateJobStatus(string $jobId, string $status, ?string $error = null, ?string $filePath = null): void
    {
        $path = "exports/jobs/{$jobId}.json";
        
        if (Storage::disk('local')->exists($path)) {
            $metadata = json_decode(Storage::disk('local')->get($path), true);
            $metadata['status'] = $status;
            $metadata['updated_at'] = now()->toDateTimeString();
            
            if ($error) {
                $metadata['error'] = $error;
            }
            
            if ($filePath) {
                $metadata['file_path'] = $filePath;
                $metadata['download_url'] = url("/api/exports/download/{$jobId}");
            }
            
            if ($status === 'completed') {
                $metadata['completed_at'] = now()->toDateTimeString();
            }
            
            Storage::disk('local')->put($path, json_encode($metadata, JSON_PRETTY_PRINT));
        }
    }
    
    private function sendExportNotification(User $user, string $type, string $format, string $filePath): void
    {
        if ($user->email) {
            Mail::to($user->email)->send(new ExportReadyMail(
                $type,
                $format,
                $filePath,
                $user->name
            ));
        }
    }
    
    public function getJobStatus(string $jobId): ?array
    {
        $path = "exports/jobs/{$jobId}.json";
        
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }
        
        $metadata = json_decode(Storage::disk('local')->get($path), true);
        
        // Add file info if exists
        if (isset($metadata['file_path']) && Storage::disk('local')->exists($metadata['file_path'])) {
            $metadata['file_size'] = Storage::disk('local')->size($metadata['file_path']);
            $metadata['file_exists'] = true;
        } else {
            $metadata['file_exists'] = false;
        }
        
        return $metadata;
    }
    
    public function getExportFile(string $jobId)
    {
        $metadata = $this->getJobStatus($jobId);
        
        if (!$metadata || !isset($metadata['file_path']) || !Storage::disk('local')->exists($metadata['file_path'])) {
            throw new \Exception('Export file not found or job does not exist');
        }
        
        if ($metadata['status'] !== 'completed') {
            throw new \Exception('Export is not yet completed');
        }
        
        return storage_path('app/' . $metadata['file_path']);
    }
    
    public function cleanupOldExports(int $days = 7): array
    {
        $exportsPath = 'exports/';
        $jobsPath = 'exports/jobs/';
        
        $deletedFiles = [];
        $deletedJobs = [];
        
        // Delete old export files
        $files = Storage::disk('local')->files($exportsPath);
        foreach ($files as $file) {
            if (Storage::disk('local')->lastModified($file) < now()->subDays($days)->timestamp) {
                Storage::disk('local')->delete($file);
                $deletedFiles[] = $file;
            }
        }
        
        // Delete old job metadata
        $jobs = Storage::disk('local')->files($jobsPath);
        foreach ($jobs as $job) {
            if (Storage::disk('local')->lastModified($job) < now()->subDays($days)->timestamp) {
                Storage::disk('local')->delete($job);
                $deletedJobs[] = $job;
            }
        }
        
        return [
            'deleted_files' => count($deletedFiles),
            'deleted_jobs' => count($deletedJobs),
            'timestamp' => now()->toDateTimeString(),
        ];
    }
    
    public function getQueueStatus(): array
    {
        return [
            'queue_size' => Queue::size('exports'),
            'failed_jobs' => \DB::table('failed_jobs')->count(),
            'pending_exports' => count(Storage::disk('local')->files('exports/jobs/')),
            'export_files_count' => count(Storage::disk('local')->files('exports/')),
            'disk_usage' => $this->getExportDiskUsage(),
        ];
    }
    
    private function getExportDiskUsage(): array
    {
        $exportsPath = 'exports/';
        $totalSize = 0;
        $fileCount = 0;
        
        $files = Storage::disk('local')->allFiles($exportsPath);
        foreach ($files as $file) {
            $totalSize += Storage::disk('local')->size($file);
            $fileCount++;
        }
        
        return [
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'file_count' => $fileCount,
            'average_size_kb' => $fileCount > 0 ? round($totalSize / $fileCount / 1024, 2) : 0,
        ];
    }
}