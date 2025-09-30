<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers for all responses
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS (HTTP Strict Transport Security) - only for HTTPS
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy - Configurable strictness
        $cspEnabled = config('app.csp_enabled', true);
        if ($cspEnabled) {
            $isProduction = app()->environment('production');
            $strictCsp = config('app.csp_strict', !$isProduction); // Default to lenient in non-production

            if ($strictCsp) {
                // Strict CSP for production
                $csp = [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com",
                    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
                    "font-src 'self' https://fonts.gstatic.com",
                    "img-src 'self' data: https: blob:",
                    "connect-src 'self'",
                    "media-src 'self'",
                    "object-src 'none'",
                    "frame-src 'none'",
                    "base-uri 'self'",
                    "form-action 'self'",
                ];
            } else {
                // Lenient CSP for development
                $csp = [
                    "default-src 'self'",
                    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://code.jquery.com https://jsdelivr.net https://unpkg.com data:",
                    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com data:",
                    "font-src 'self' https://fonts.gstatic.com data:",
                    "img-src 'self' data: https: blob:",
                    "connect-src 'self' ws: wss: https:",
                    "media-src 'self'",
                    "object-src 'none'",
                    "frame-src 'none'",
                    "base-uri 'self'",
                    "form-action 'self'",
                ];
            }

            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // Remove server information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
