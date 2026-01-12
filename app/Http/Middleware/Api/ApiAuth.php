<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sanctum middleware
        if (!auth()->guard('sanctum')->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
                'errors' => ['token' => 'Invalid or missing authentication token.']
            ], 401);
        }
        
        return $next($request);
    }
}