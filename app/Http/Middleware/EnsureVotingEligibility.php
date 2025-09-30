<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureVotingEligibility
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.login');
        }

        // Check if user is approved and has completed KYC
        if ($user->status !== 'approved') {
            $message = match($user->status) {
                'review' => 'Your account is under review. Please wait for approval.',
                'rejected' => 'Your account has been rejected. Please contact support.',
                default => 'Complete your KYC verification to access voting features.'
            };

            return redirect()->route('voter.dashboard')
                ->with('warning', $message);
        }

        return $next($request);
    }
}
