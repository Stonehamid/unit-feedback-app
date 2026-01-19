<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Export\ExportQueueService;

class ExportDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    
    protected $jobId;
    protected $type;
    protected $data;
    protected $userId;
    protected $format;
    
    public function __construct(string $jobId, string $type, array $data, int $userId, string $format)
    {
        $this->jobId = $jobId;
        $this->type = $type;
        $this->data = $data;
        $this->userId = $userId;
        $this->format = $format;
    }
    
    public function handle(ExportQueueService $exportService): void
    {
        $exportService->processExport(
            $this->jobId,
            $this->type,
            $this->data,
            $this->userId,
            $this->format
        );
    }
    
    public function failed(\Throwable $exception): void
    {
        // Log failure
        \Log::error("Export job failed: {$this->jobId}", [
            'error' => $exception->getMessage(),
            'type' => $this->type,
            'user_id' => $this->userId,
        ]);
    }
}