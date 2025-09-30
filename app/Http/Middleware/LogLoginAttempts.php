<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Security\LoginAttempt;
use Illuminate\Support\Facades\Auth;

class LogLoginAttempts
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log login attempts for login routes
        if ($this->isLoginRoute($request)) {
            $this->logAttempt($request, $response);
        }

        return $response;
    }

    private function isLoginRoute(Request $request): bool
    {
        return $request->is('admin/login') || 
               $request->is('observer/login') || 
               $request->is('login') ||
               $request->routeIs('admin.login') ||
               $request->routeIs('observer.login') ||
               $request->routeIs('login');
    }

    private function logAttempt(Request $request, $response)
    {
        // Determine if login was successful based on response
        $successful = $this->isSuccessfulLogin($request, $response);
        
        // Get user info if successful
        $userId = null;
        $userType = null;
        $email = $request->input('email');

        if ($successful) {
            if (Auth::guard('admin')->check()) {
                $userId = Auth::guard('admin')->id();
                $userType = 'admin';
            } elseif (Auth::guard('observer')->check()) {
                $userId = Auth::guard('observer')->id();
                $userType = 'observer';
            } elseif (Auth::check()) {
                $userId = Auth::id();
                $userType = 'user';
            }
        }

        try {
            LoginAttempt::create([
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => $successful,
                'user_id' => $userId,
                'user_type' => $userType,
                'attempted_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to avoid breaking login flow
        }
    }

    private function isSuccessfulLogin(Request $request, $response): bool
    {
        // Check if any guard is authenticated after the request
        return Auth::guard('admin')->check() || 
               Auth::guard('observer')->check() || 
               Auth::check();
    }
}