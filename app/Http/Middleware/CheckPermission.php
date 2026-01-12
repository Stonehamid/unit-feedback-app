<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        // Middleware global untuk permission
        // Contoh: ->middleware('permission:create_rating')
        
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
                'data' => null,
                'errors' => ['auth' => 'Please login first.']
            ], 401);
        }
        
        $user = auth()->user();
        
        // Logic permission bisa disesuaikan
        // Contoh sederhana: admin punya semua permission
        if ($user->role === 'admin') {
            return $next($request);
        }
        
        // Untuk reviewer/user, cek permission
        // Asumsi: ada kolom 'permissions' (JSON) di table users
        $userPermissions = json_decode($user->permissions ?? '[]', true);
        
        if (!in_array($permission, $userPermissions)) {
            return response()->json([
                'success' => false,
                'message' => 'Permission denied.',
                'data' => null,
                'errors' => ['permission' => "You don't have permission to: {$permission}"]
            ], 403);
        }
        
        return $next($request);
    }
}