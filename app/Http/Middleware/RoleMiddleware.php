<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            // Untuk web, redirect ke login
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
            return redirect()->route('login');
        }
        
        // Check if user has any of the required roles
        if (!in_array($user->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient permissions',
                    'errors' => ['role' => 'You do not have permission to access this resource'],
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        return $next($request);
    }
}