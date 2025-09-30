<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Auth\MFAService;

class SensitiveOperationMiddleware
{
    protected MFAService $mfaService;

    public function __construct(MFAService $mfaService)
    {
        $this->mfaService = $mfaService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Check if MFA is enabled for admin users
        if (in_array($user->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $this->mfaService->isMFAEnabled($user)) {

            // Check if MFA was verified recently (within last 30 minutes)
            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                // Redirect to MFA verification page
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'MFA verification required'], 403);
                }

                return redirect()->route('admin.mfa.verify')
                    ->with('intended', $request->fullUrl());
            }
        }

        return $next($request);
    }
}
