<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Route middleware classes
use App\Http\Middleware\CompanyAccess;
use App\Http\Middleware\EnsureSupport;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',   // ➜ add this line
        commands: __DIR__.'/../routes/console.php',
        health: 'up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'company' => \App\Http\Middleware\CompanyAccess::class,
            'support' => \App\Http\Middleware\EnsureSupport::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();