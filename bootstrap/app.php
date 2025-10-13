<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

// Aliases bawaan Laravel untuk auth & verified
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // ✅ daftar alias (tambahkan, tidak menghapus yang lain)
        $middleware->alias([
            'auth'     => Authenticate::class,
            'verified' => EnsureEmailIsVerified::class,
            'role'     => \App\Http\Middleware\EnsureRole::class,
        ]);

        // (opsional) kalau suatu saat mau menambah web group middleware, taruh di sini:
        // $middleware->web(append: [ ... ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // biarkan default
    })
    ->create();
