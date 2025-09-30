<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'guard',
        'metadata',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'metadata' => 'array',
        'attempted_at' => 'datetime',
    ];

    /**
     * Scopes
     */
    public function scopeRecent($query, $minutes = 15)
    {
        return $query->where('attempted_at', '>=', Carbon::now()->subMinutes($minutes));
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    public function scopeByEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    public function scopeByGuard($query, $guard)
    {
        return $query->where('guard', $guard);
    }

    /**
     * Helper methods
     */
    public static function recordAttempt(string $email, string $ip, ?string $userAgent = null, bool $successful = false, string $guard = 'web', array $metadata = []): self
    {
        return static::create([
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'successful' => $successful,
            'guard' => $guard,
            'metadata' => $metadata,
            'attempted_at' => now(),
        ]);
    }

    public static function getFailedAttemptsCount(string $email, int $minutes = 15): int
    {
        return static::byEmail($email)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    public static function getFailedAttemptsCountByIp(string $ip, int $minutes = 15): int
    {
        return static::byIp($ip)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    public static function shouldBlockEmail(string $email, int $maxAttempts = 5, int $minutes = 15): bool
    {
        return static::getFailedAttemptsCount($email, $minutes) >= $maxAttempts;
    }

    public static function shouldBlockIp(string $ip, int $maxAttempts = 10, int $minutes = 15): bool
    {
        return static::getFailedAttemptsCountByIp($ip, $minutes) >= $maxAttempts;
    }

    public static function getBlockDuration(int $attemptCount): int
    {
        // Exponential backoff: 1min, 5min, 15min, 60min, 240min
        $durations = [1, 5, 15, 60, 240];

        if ($attemptCount <= count($durations)) {
            return $durations[$attemptCount - 1];
        }

        return end($durations); // Max duration
    }

    public static function cleanupOldAttempts(int $days = 30): int
    {
        return static::where('attempted_at', '<', Carbon::now()->subDays($days))->delete();
    }
}
