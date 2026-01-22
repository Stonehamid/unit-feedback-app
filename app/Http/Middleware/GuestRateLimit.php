<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class GuestRateLimit
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $this->maxAttempts())) {
            return $this->buildRateLimitResponse($key);
        }

        RateLimiter::hit($key, $this->decayMinutes() * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders($response, $key);
    }

    protected function resolveRequestSignature(Request $request): string
    {
        return sha1(
            $request->ip() .
            '|' .
            $request->path() .
            '|' .
            ($request->userAgent() ?? 'unknown')
        );
    }

    protected function maxAttempts(): int
    {
        return config('ratelimit.guest.max_attempts', 100);
    }

    protected function decayMinutes(): int
    {
        return config('ratelimit.guest.decay_minutes', 1);
    }

    protected function buildRateLimitResponse(string $key): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            'retry_after' => $retryAfter,
            'retry_after_human' => $this->secondsToHuman($retryAfter),
        ], 429);
    }

    protected function addRateLimitHeaders(Response $response, string $key): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $this->maxAttempts(),
            'X-RateLimit-Remaining' => max(0, $this->maxAttempts() - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => now()->addMinutes($this->decayMinutes())->getTimestamp(),
        ]);

        return $response;
    }

    protected function secondsToHuman(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} detik";
        }

        $minutes = floor($seconds / 60);
        return "{$minutes} menit";
    }
}