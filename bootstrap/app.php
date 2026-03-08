<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // First middleware configuration
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // API middleware configuration
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Add CSRF token exclusion for API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',           // Exclude all API routes
            'login',           // Also exclude login
            'activate/*',      // Exclude activation routes
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();