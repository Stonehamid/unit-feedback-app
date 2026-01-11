<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReviewerOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Admin juga bisa melakukan review
        if (!auth()->user() || (!auth()->user()->isReviewer() && !auth()->user()->isAdmin())) {
            return response()->json(['message' => 'Forbidden. Reviewer access only.'], 403);
        }

        return $next($request);
    }
}