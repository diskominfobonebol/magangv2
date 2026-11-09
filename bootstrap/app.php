<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'force.password.reset' => \App\Http\Middleware\ForcePasswordReset::class,
            'password.changed' => \App\Http\Middleware\EnsurePasswordChanged::class,
            'two_factor' => \App\Http\Middleware\EnsureTwoFactorEnrolled::class,
            '2fa.enrolled' => \App\Http\Middleware\EnsureTwoFactorEnrolled::class,
            '2fa.verified' => \App\Http\Middleware\EnsureTwoFactorAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
