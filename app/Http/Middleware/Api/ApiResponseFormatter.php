<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiResponseFormatter
{
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        $response = $next($request);
        
        // Skip jika bukan response JSON atau sudah diformat
        if (!$response instanceof Response) {
            return $response;
        }
        
        // Get original content
        $originalContent = $response->original;
        
        // Jika sudah berupa response JSON dengan format kita, skip
        if (is_array($originalContent) && isset($originalContent['success'])) {
            return $response;
        }
        
        // Handle exception/error response
        if ($response->getStatusCode() >= 400) {
            // Untuk error validation Laravel
            if (isset($originalContent['errors'])) {
                $errors = $originalContent['errors'];
                $message = $originalContent['message'] ?? 'Validation Error';
            } else {
                $errors = $originalContent['error'] ?? ($originalContent['message'] ?? 'Error occurred');
                $message = 'Error';
            }
            
            $formatted = [
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => $errors
            ];
        } else {
            // Success response
            $formatted = [
                'success' => true,
                'message' => $originalContent['message'] ?? 'Success',
                'data' => $originalContent['data'] ?? $originalContent,
                'errors' => null
            ];
            
            // Jika response adalah view atau redirect, return as is
            if (!is_array($originalContent) && !is_object($originalContent)) {
                return $response;
            }
        }
        
        return response()->json($formatted, $response->getStatusCode());
    }
}