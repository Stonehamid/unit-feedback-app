<?php

namespace App\Http\Middleware\Reviewer;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // User bisa admin atau reviewer
        $allowedRoles = ['admin', 'reviewer'];
        
        if (!auth()->check() || !in_array(auth()->user()->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
                'data' => null,
                'errors' => ['role' => 'You need to be admin or reviewer to access this.']
            ], 403);
        }
        
        return $next($request);
    }
}