<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObserverAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('observer')->check()) {
            return redirect()->route('observer.login');
        }

        return $next($request);
    }
}