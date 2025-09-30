<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        \Illuminate\Support\Facades\Log::info('AdminAuth middleware called', [
            'url' => $request->url(),
            'method' => $request->method(),
            'admin_check' => Auth::guard('admin')->check(),
            'admin_id' => Auth::guard('admin')->id(),
            'admin_user' => Auth::guard('admin')->user(),
            'is_livewire' => $request->hasHeader('X-Livewire'),
            'expects_json' => $request->expectsJson(),
        ]);

        if (!Auth::guard('admin')->check()) {
            \Illuminate\Support\Facades\Log::info('AdminAuth middleware - admin not authenticated, redirecting');
            if ($request->expectsJson() || $request->hasHeader('X-Livewire')) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('admin.login');
        }

        \Illuminate\Support\Facades\Log::info('AdminAuth middleware - admin authenticated, proceeding');
        return $next($request);
    }
}