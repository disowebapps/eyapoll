<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSignedUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the URL has a signature parameter
        if (!$request->has('signature')) {
            return response()->json([
                'error' => 'Invalid URL',
                'message' => 'This link requires a valid signature.',
            ], 403);
        }

        // Laravel's signed URLs are validated automatically by the framework
        // But we can add additional validation here if needed

        // Check if the signature is valid (Laravel does this automatically)
        if (!$request->hasValidSignature()) {
            return response()->json([
                'error' => 'Invalid or expired signature',
                'message' => 'This link has expired or is invalid.',
            ], 403);
        }

        return $next($request);
    }
}
