<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin access only.'], 403);
        }

        return $next($request);
    }
}