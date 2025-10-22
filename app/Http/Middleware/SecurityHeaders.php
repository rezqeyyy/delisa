<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $isLocal = app()->environment('local');

        // COMMON
        $script      = ["'self'"];
        $scriptElem  = ["'self'"];
        $style       = ["'self'"];
        $styleElem   = ["'self'", 'https://fonts.googleapis.com'];
        $font        = ["'self'", 'https://fonts.gstatic.com'];
        $img         = ["'self'", 'data:', 'blob:'];
        $connect     = ["'self'"];

        if ($isLocal) {
            $viteHttp = ['http://localhost:5173', 'http://127.0.0.1:5173'];
            $viteWs   = ['ws://localhost:5173', 'ws://127.0.0.1:5173'];

            $script      = array_merge($script, $viteHttp);
            $scriptElem  = array_merge($scriptElem, $viteHttp);
            $styleElem   = array_merge($styleElem, $viteHttp);
            $font        = array_merge($font, $viteHttp);
            $connect     = array_merge($connect, $viteHttp, $viteWs);

            // DEV ONLY (HMR & sourcemap)
            $style[]      = "'unsafe-inline'";
            $styleElem[]  = "'unsafe-inline'";
            $script[]     = "'unsafe-eval'";
            $scriptElem[] = "'unsafe-eval'";
        }

        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "script-src "       . implode(' ', $script),
            "script-src-elem "  . implode(' ', $scriptElem),
            "style-src "        . implode(' ', $style),
            "style-src-elem "   . implode(' ', $styleElem),
            "font-src "         . implode(' ', $font),
            "img-src "          . implode(' ', $img),
            "connect-src "      . implode(' ', $connect),
            "upgrade-insecure-requests",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
