<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'guest.rate_limit' => \App\Http\Middleware\GuestRateLimit::class,
            'visitor.cooldown' => \App\Http\Middleware\VisitorCooldown::class,
            'unit.access' => \App\Http\Middleware\UnitAccessMiddleware::class,
        ]);

        $middleware->group('admin.api', [
            'auth:sanctum',
            'admin',
            'throttle:60,1',
        ]);

        $middleware->group('visitor.api', [
            'guest.rate_limit',
            'throttle:100,1',
        ]);

        $middleware->group('rating.submission', [
            'visitor.cooldown',
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->appendToGroup('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Illuminate\Auth\AuthenticationException $e) {
            \Log::warning('Authentication failed', [
                'message' => $e->getMessage(),
                'ip' => request()->ip(),
                'url' => request()->fullUrl(),
            ]);
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login.',
                    'error' => 'UNAUTHENTICATED'
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                    'error' => 'NOT_FOUND'
                ], 404);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed',
                    'error' => 'METHOD_NOT_ALLOWED'
                ], 405);
            }
        });

        $exceptions->render(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $retryAfter,
                    'error' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
            }
        });

        if (app()->environment('local', 'development')) {
            $exceptions->render(function (\Throwable $e, $request) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Internal server error',
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace(),
                    ], 500);
                }
            });
        }
    })
    ->create();