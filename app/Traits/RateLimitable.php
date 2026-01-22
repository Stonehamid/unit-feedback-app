<?php

namespace App\Traits;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

trait RateLimitable
{
    protected function ensureIsNotRateLimited(string $key, int $maxAttempts, int $decaySeconds = 60)
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            abort(429, "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
        }

        RateLimiter::hit($key, $decaySeconds);
    }

    protected function checkRateLimit(string $key, int $maxAttempts)
    {
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $retryAfter = RateLimiter::availableIn($key);
        
        return [
            'remaining' => $remaining,
            'retry_after' => $retryAfter,
            'limited' => $remaining <= 0,
        ];
    }

    protected function clearRateLimit(string $key)
    {
        RateLimiter::clear($key);
    }

    protected function getRateLimitKey(string $prefix, $identifier = null)
    {
        if ($identifier === null) {
            $identifier = request()->ip();
        }
        
        return Str::lower($prefix) . '|' . $identifier;
    }
}