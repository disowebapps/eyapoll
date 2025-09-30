<?php

namespace App\Listeners;

use App\Models\Security\LoginAttempt;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Attempting;

class LogLoginAttempt
{
    public function handleLogin(Login $event)
    {
        $this->logAttempt($event->user, true);
    }

    public function handleFailed(Failed $event)
    {
        $this->logAttempt(null, false, $event->credentials['email'] ?? null);
    }

    private function logAttempt($user = null, bool $successful = false, string $email = null)
    {
        try {
            LoginAttempt::create([
                'email' => $email ?? ($user->email ?? 'unknown'),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'user_agent' => request()->userAgent(),
                'successful' => $successful,
                'guard' => $this->getGuardName($user),
                'metadata' => json_encode([
                    'user_id' => $user->id ?? null,
                    'user_type' => $this->getUserType($user),
                ]),
                'attempted_at' => now(),
            ]);
            
            // Clear dashboard cache for real-time updates
            \Cache::forget('admin_dashboard_stats_' . 1);
            \Cache::forget('admin_dashboard_charts_' . 1);
        } catch (\Exception $e) {
            // Silently fail
        }
    }

    private function getUserType($user): ?string
    {
        if (!$user) return null;
        
        return match(get_class($user)) {
            'App\Models\Admin' => 'admin',
            'App\Models\Observer' => 'observer',
            'App\Models\User' => 'user',
            default => 'unknown'
        };
    }

    private function getGuardName($user): string
    {
        if (!$user) return 'web';
        
        return match(get_class($user)) {
            'App\Models\Admin' => 'admin',
            'App\Models\Observer' => 'observer',
            'App\Models\User' => 'web',
            default => 'web'
        };
    }
}