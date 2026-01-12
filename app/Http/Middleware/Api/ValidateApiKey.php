<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        // Middleware ini optional, karena kita pake Sanctum
        // Tapi dibuat untuk backup atau special case
        
        // Jika ada API key di header, validasi
        $apiKey = $request->header('X-API-Key');
        
        if ($apiKey) {
            // Validasi API key jika diperlukan
            // Contoh: cek di database atau config
            $validKeys = config('app.api_keys', []);
            
            if (!in_array($apiKey, $validKeys)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API Key.',
                    'data' => null,
                    'errors' => ['api_key' => 'The provided API key is invalid.']
                ], 401);
            }
        }
        
        return $next($request);
    }
}