<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('candidate')->check()) {
            return redirect()->route('candidate.login');
        }

        return $next($request);
    }
}