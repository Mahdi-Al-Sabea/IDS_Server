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
        $middleware->alias([
            'checkauth' => \App\Http\Middleware\CheckAuthentication::class,
            'checkadmin' => \App\Http\Middleware\CheckAdmin::class,
            'checkemployee' => \App\Http\Middleware\CheckEmployee::class,
            'checkrole' => \App\Http\Middleware\CheckRole::class,
            'checktokenexpiry' => \App\Http\Middleware\CheckTokenExpiry::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
