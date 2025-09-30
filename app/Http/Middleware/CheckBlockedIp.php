<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Security\IpBlock;
use App\Services\Audit\AuditLogService;

class CheckBlockedIp
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ipAddress = $request->ip();

        // Check if IP is blocked
        if (IpBlock::isBlocked($ipAddress)) {
            // Log the blocked attempt
            $this->auditLog->logSystemAction('blocked_ip_access_attempt', null, [
                'ip_address' => $ipAddress,
                'requested_url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            // Return 403 Forbidden
            return response()->json([
                'error' => 'Access denied',
                'message' => 'Your IP address has been blocked due to suspicious activity.',
            ], 403);
        }

        return $next($request);
    }
}
