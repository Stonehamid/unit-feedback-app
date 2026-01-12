<?php

namespace App\Http\Middleware\Reviewer;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewerOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya reviewer murni (bukan admin)
        if (!auth()->check() || auth()->user()->role !== 'reviewer') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Reviewer access only.',
                'data' => null,
                'errors' => ['role' => 'This route is for reviewers only.']
            ], 403);
        }
        
        return $next($request);
    }
}