<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user authenticated dan role admin
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
                'data' => null,
                'errors' => ['access_denied' => 'You do not have admin privileges.']
            ], 403);
        }

        return $next($request);
    }
}