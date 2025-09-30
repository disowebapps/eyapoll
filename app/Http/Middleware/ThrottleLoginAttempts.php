<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Security\SecurityService;

class ThrottleLoginAttempts
{
    public function __construct(
        private SecurityService $securityService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->input('email');
        $ip = $request->ip();

        // Check if login should be blocked
        $blockCheck = $this->securityService->shouldBlockLogin($email, $ip);

        if ($blockCheck['blocked']) {
            return response()->json([
                'error' => 'Too many login attempts',
                'message' => 'Your account has been temporarily locked due to too many failed login attempts. Please try again later.',
                'retry_after_minutes' => $blockCheck['block_duration_minutes'],
            ], 429);
        }

        $response = $next($request);

        // Record the login attempt after processing
        $successful = $response->getStatusCode() === 200;
        $this->securityService->recordLoginAttempt(
            $email,
            $ip,
            $request->userAgent(),
            $successful,
            'web' // Could be determined from request
        );

        return $response;
    }
}
