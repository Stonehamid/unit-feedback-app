<?php

namespace App\Http\Middleware;

use App\Models\Rating;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VisitorCooldown
{
    public function handle(Request $request, Closure $next): Response
    {
        $unitId = $request->route('unit');
        $sessionId = $request->session()->getId();

        if (!$unitId) {
            return $next($request);
        }

        $hasRecentRating = Rating::where('unit_id', $unitId)
            ->where('session_id', $sessionId)
            ->whereDate('created_at', today())
            ->exists();

        if ($hasRecentRating) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberikan rating untuk unit ini hari ini. Silakan kembali besok.',
                'cooldown_until' => now()->addDay()->toDateTimeString(),
                'can_rate_again_at' => now()->addDay()->toDateTimeString(),
            ], 429);
        }

        return $next($request);
    }
}