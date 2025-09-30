<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\Auth\UserStatus;
use Symfony\Component\HttpFoundation\Response;

class EnsureKycAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('auth.login');
        }
        
        \Log::info('KYC Middleware', [
            'user_id' => $user->id,
            'status' => $user->status->value,
            'route' => $request->route()->getName()
        ]);
        
        // Allow access to KYC page for pending/review users
        if ($request->routeIs('voter.kyc')) {
            return $next($request);
        }
        
        // Allow logout
        if ($request->routeIs('logout')) {
            return $next($request);
        }
        
        // Allow approved and accredited users first
        if (in_array($user->status, [UserStatus::APPROVED, UserStatus::ACCREDITED])) {
            \Log::info('User allowed access', ['status' => $user->status->value]);
            return $next($request);
        }
        
        // Redirect pending users to KYC page
        if ($user->status === UserStatus::PENDING) {
            return redirect()->route('voter.kyc')
                ->with('info', 'Please upload your KYC documents to continue.');
        }
        
        // Redirect review users to KYC page (read-only)
        if ($user->status === UserStatus::REVIEW) {
            return redirect()->route('voter.kyc')
                ->with('info', 'Your documents are under review. Please wait for approval.');
        }
        
        // Block all other statuses
        \Log::warning('User blocked', ['status' => $user->status->value]);
        return redirect()->route('voter.kyc')
            ->with('error', 'Access denied. Please contact support.');
    }
}
