<?php

namespace App\Services\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminActionLogger
{
    public function log(string $action, array $data = []): void
    {
        $logData = array_merge([
            'admin_id' => Auth::id(),
            'admin_name' => Auth::user()->name ?? 'System',
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);
        
        Log::channel('admin')->info('Admin Action: ' . $action, $logData);
    }
    
    public function logBulkAction(string $action, array $ids, array $additionalData = []): void
    {
        $this->log('bulk ' . $action, array_merge([
            'item_count' => count($ids),
            'item_ids' => $ids,
        ], $additionalData));
    }
}