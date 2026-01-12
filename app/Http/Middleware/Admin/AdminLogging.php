<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Log hanya jika user adalah admin dan method bukan GET
        if (auth()->check() && 
            auth()->user()->role === 'admin' && 
            !in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            
            $logData = [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'ip' => $request->ip(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
                'request_data' => $this->filterSensitiveData($request->all()),
                'status_code' => $response->getStatusCode(),
                'timestamp' => now()->toDateTimeString()
            ];
            
            // Log ke file (channel 'admin' di config/logging.php)
            Log::channel('admin')->info('Admin Action', $logData);
        }
        
        return $response;
    }
    
    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'api_key', 'secret', 'authorization'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '*****';
            }
        }
        
        return $data;
    }
}