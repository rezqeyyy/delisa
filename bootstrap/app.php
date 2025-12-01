<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

use App\Http\Middleware\SecurityHeaders;

use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Auth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth'     => \Illuminate\Auth\Middleware\Authenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role'     => \App\Http\Middleware\EnsureRole::class,
        ]);

        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 0. Jangan abaikan lagi HttpException (termasuk 419)
        $exceptions->stopIgnoring(HttpException::class);

        // 1. Handler unauthenticated (session habis / belum login)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            logger()->warning('Unauthenticated request', [
                'exception' => class_basename($e),
                'url'       => $request->fullUrl(),
                'ip'        => $request->ip(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            if ($request->is('pasien/*')) {
                return redirect()
                    ->route('pasien.login')
                    ->withErrors([
                        'nik' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    ]);
            }

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                ]);
        });

        // 2. Handler khusus response 419 (Page Expired / CSRF)
        $exceptions->respond(function (Response $response, \Throwable $e, Request $request) {
            // Kalau bukan 419 → biarkan handler default
            if ($response->getStatusCode() !== 419) {
                return $response;
            }

            logger()->warning('Token mismatch / CSRF failure', [
                'exception' => class_basename($e),
                'url'       => $request->fullUrl(),
                'ip'        => $request->ip(),
            ]);

            // 🔴 PAKSA LOGOUT: anggap sesi sudah tidak sehat
            if (Auth::check()) {
                Auth::logout();
            }

            // Invalidate seluruh session + regenerate CSRF token
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Kalau request JSON (API / XHR expecting JSON)
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi telah berakhir atau token tidak valid. Silakan muat ulang halaman.',
                ], 419);
            }

            // Prefix /pasien → redirect ke login pasien
            if ($request->is('pasien/*')) {
                return redirect()
                    ->route('pasien.login')
                    ->withErrors([
                        'nik' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    ]);
            }

            // Selain itu → redirect ke login petugas
            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                ]);
        });
    })
    ->create();
