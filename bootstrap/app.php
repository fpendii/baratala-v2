<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Ini untuk mengecualikan CSRF agar Flutter tidak kena error 403
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // 2. Ini alias yang sudah kamu buat sebelumnya
        $middleware->alias(
            [
                'login' => App\Http\Middleware\AuthCheck::class,
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
