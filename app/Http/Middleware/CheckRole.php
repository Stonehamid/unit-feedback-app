<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Middleware global dengan parameter roles
        // Contoh penggunaan: ->middleware('role:admin,reviewer')
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'data' => null,
                'errors' => ['auth' => 'Please login first.']
            ], 401);
        }
        
        $userRole = auth()->user()->role;
        
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions.',
                'data' => null,
                'errors' => ['role' => 'Required roles: ' . implode(', ', $roles)]
            ], 403);
        }
        
        return $next($request);
    }
}