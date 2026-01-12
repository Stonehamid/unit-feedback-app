<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api/admin')
                ->group(base_path('routes/admin.php'));

            Route::middleware('api')
                ->group(base_path('routes/reviewer-public.php'));

            /*
            Route::middleware('api')
                ->prefix('api/reviewer')
                ->group(base_path('routes/reviewer.php'));
            */
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ============================================
        // REGISTER MIDDLEWARE ALIAS
        // ============================================
        $middleware->alias([
            // Admin middleware
            'admin.access' => \App\Http\Middleware\Admin\AdminAccess::class,
            'admin.logging' => \App\Http\Middleware\Admin\AdminLogging::class,
            'admin.only' => \App\Http\Middleware\Admin\AdminOnly::class,

            // API middleware  
            'api.auth' => \App\Http\Middleware\Api\ApiAuth::class,
            'api.format' => \App\Http\Middleware\Api\ApiResponseFormatter::class,
            'api.throttle' => \App\Http\Middleware\Api\ApiThrottle::class,
            'api.validate_key' => \App\Http\Middleware\Api\ValidateApiKey::class,

            // Reviewer middleware
            'reviewer.access' => \App\Http\Middleware\Reviewer\ReviewerAccess::class,
            'reviewer.only' => \App\Http\Middleware\Reviewer\ReviewerOnly::class,

            // Global custom middleware
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // ============================================
        // MIDDLEWARE GROUPS
        // ============================================
    
        // Web group (default Laravel)
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // API group (default Laravel)
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Custom API group with response formatter
        $middleware->appendToGroup('api.formatted', [
            'api.format',  // Our custom formatter
        ]);

        // Admin API group
        $middleware->appendToGroup('admin.api', [
            'auth:sanctum',
            'admin.access',
            'admin.logging',
        ]);

        // Reviewer API group  
        $middleware->appendToGroup('reviewer.api', [
            'auth:sanctum',
            'reviewer.access',
        ]);

        // ============================================
        // GLOBAL MIDDLEWARE
        // ============================================
    
        // Global middleware untuk semua web routes
        $middleware->web([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Global middleware untuk semua API routes
        // Note: api.format sudah di append ke group 'api.formatted'
        $middleware->api([
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // ============================================
        // MIDDLEWARE PRIORITY (optional)
        // ============================================
        $middleware->priority([
            \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \Illuminate\Routing\Middleware\ThrottleRequestsWithRedis::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'admin.logging',  // Our custom logging
            'api.format',     // Our response formatter
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ============================================
        // CUSTOM EXCEPTION HANDLING
        // ============================================
    
        // Authentication exception (401)
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'data' => null,
                    'errors' => ['auth' => 'Authentication required.']
                ], 401);
            }
        });

        // Authorization exception (403)
        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have permission.',
                    'data' => null,
                    'errors' => ['permission' => $e->getMessage() ?: 'Access denied.']
                ], 403);
            }
        });

        // Validation exception (422)
        $exceptions->render(function (Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'data' => null,
                    'errors' => $e->errors()
                ], 422);
            }
        });

        // Model not found exception (404)
        $exceptions->render(function (Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found.',
                    'data' => null,
                    'errors' => ['resource' => 'The requested resource does not exist.']
                ], 404);
            }
        });

        // Method not allowed exception (405)
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Method not allowed.',
                    'data' => null,
                    'errors' => ['method' => 'The HTTP method is not supported for this route.']
                ], 405);
            }
        });

        // Route not found exception (404)
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Endpoint not found.',
                    'data' => null,
                    'errors' => ['endpoint' => 'The requested API endpoint does not exist.']
                ], 404);
            }
        });

        // Too many requests exception (429)
        $exceptions->render(function (Illuminate\Http\Exceptions\ThrottleRequestsException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests.',
                    'data' => null,
                    'errors' => ['throttle' => 'Please try again later.']
                ], 429);
            }
        });

        // General exception handler (500)
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $response = [
                    'success' => false,
                    'message' => 'Internal server error.',
                    'data' => null,
                    'errors' => ['server' => 'Something went wrong.']
                ];

                // Add debug info in local environment
                if (config('app.debug')) {
                    $response['debug'] = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTrace()
                    ];
                }

                return response()->json($response, 500);
            }
        });
    })
    ->create();