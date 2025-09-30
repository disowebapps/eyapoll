<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('admin')->check() || !auth('admin')->user()->is_super_admin) {
            abort(403, 'Super Admin access required');
        }

        return $next($request);
    }
}