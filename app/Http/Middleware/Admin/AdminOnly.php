<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Lebih strict: hanya admin, tidak ada pengecualian
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'data' => null,
                'errors' => ['auth' => 'Please login first.']
            ], 401);
        }
        
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Strict admin access only.',
                'data' => null,
                'errors' => ['forbidden' => 'This route is for administrators only.']
            ], 403);
        }
        
        return $next($request);
    }
}