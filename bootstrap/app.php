<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\Admin;
use App\Http\Middleware\Kasir;
use App\Http\Middleware\CekRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',       // <-- Routing API terdaftar
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '/api',                      // <-- Prefix URL /api
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Pendaftaran Middleware Group untuk API (Sanctum & Throttle)[cite: 24, 25]
        $middleware->appendToGroup('api', [
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Alias Middleware Custom Anda
        $middleware->alias([
            'admin' => Admin::class,
            'kasir' => Kasir::class,
            'role'  => CekRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();