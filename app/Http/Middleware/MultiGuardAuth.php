<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MultiGuardAuth
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                return $next($request);
            }
        }

        // Redirect based on intended route context
        if ($request->is('admin/*')) {
            return redirect()->route('admin.login');
        }
        
        return redirect()->route('login');
    }
}