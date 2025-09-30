<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next, $guard = null)
    {
        $user = Auth::guard($guard)->user();
        
        if (!$user || !$user->hasVerifiedEmail()) {
            return redirect()->route($this->getVerificationRoute($guard));
        }

        return $next($request);
    }

    private function getVerificationRoute($guard)
    {
        return match($guard) {
            'admin' => 'admin.verification.notice',
            'candidate' => 'candidate.verification.notice',
            'observer' => 'observer.verification.notice',
            default => 'voter.verification.notice',
        };
    }
}