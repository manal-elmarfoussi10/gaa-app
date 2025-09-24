<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Route middleware classes
use App\Http\Middleware\CompanyAccess;
use App\Http\Middleware\EnsureSupport;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Route middleware aliases used in routes/web.php
        $middleware->alias([
            'company' => CompanyAccess::class,
            'support' => EnsureSupport::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // customize reporting/handlers if you need
    })
    ->create();