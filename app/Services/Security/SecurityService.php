<?php

namespace App\Services\Security;

use App\Models\Security\LoginAttempt;
use App\Models\Security\IpBlock;
use App\Services\Audit\AuditLogService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class SecurityService
{
    public function __construct(
        private AuditLogService $auditLog
    ) {}

    /**
     * Record a login attempt
     */
    public function recordLoginAttempt(string $email, string $ip, ?string $userAgent = null, bool $successful = false, string $guard = 'web', array $metadata = []): void
    {
        LoginAttempt::recordAttempt($email, $ip, $userAgent, $successful, $guard, $metadata);

        // Log security events
        if (!$successful) {
            $this->auditLog->logSystemAction('failed_login_attempt', null, [
                'email' => $email,
                'ip_address' => $ip,
                'guard' => $guard,
                'user_agent' => $userAgent,
            ]);
        }
    }

    /**
     * Check if login should be blocked due to brute force
     */
    public function shouldBlockLogin(string $email, string $ip): array
    {
        $emailBlocked = LoginAttempt::shouldBlockEmail($email);
        $ipBlocked = LoginAttempt::shouldBlockIp($ip) || IpBlock::isBlocked($ip);

        $blockDuration = 0;
        if ($emailBlocked) {
            $attemptCount = LoginAttempt::getFailedAttemptsCount($email);
            $blockDuration = LoginAttempt::getBlockDuration($attemptCount);
        } elseif ($ipBlocked) {
            $attemptCount = LoginAttempt::getFailedAttemptsCountByIp($ip);
            $blockDuration = LoginAttempt::getBlockDuration($attemptCount);
        }

        return [
            'blocked' => $emailBlocked || $ipBlocked,
            'reason' => $emailBlocked ? 'email_brute_force' : ($ipBlocked ? 'ip_blocked' : null),
            'block_duration_minutes' => $blockDuration,
        ];
    }

    /**
     * Block an IP address
     */
    public function blockIp(string $ipAddress, string $reason, ?string $blockedBy = null, ?int $durationMinutes = null): void
    {
        IpBlock::blockIp($ipAddress, $reason, $blockedBy, $durationMinutes);

        $this->auditLog->logSystemAction('ip_blocked', null, [
            'ip_address' => $ipAddress,
            'reason' => $reason,
            'blocked_by' => $blockedBy,
            'duration_minutes' => $durationMinutes,
        ]);
    }

    /**
     * Unblock an IP address
     */
    public function unblockIp(string $ipAddress): void
    {
        $unblocked = IpBlock::unblockIp($ipAddress);

        if ($unblocked) {
            $this->auditLog->logSystemAction('ip_unblocked', null, [
                'ip_address' => $ipAddress,
            ]);
        }
    }

    /**
     * Generate signed URL for sensitive actions
     */
    public function generateSignedUrl(string $route, array $parameters = [], int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addMinutes($expirationMinutes),
            $parameters
        );
    }

    /**
     * Validate signed URL (middleware will handle this, but this is for manual validation)
     */
    public function validateSignedUrl(string $fullUrl): bool
    {
        try {
            $parsedUrl = parse_url($fullUrl);
            parse_str($parsedUrl['query'] ?? '', $queryParams);

            return isset($queryParams['signature']) &&
                   isset($queryParams['expires']) &&
                   now()->timestamp <= $queryParams['expires'];
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check rate limit for an action
     */
    public function checkRateLimit(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        return !RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Hit rate limiter
     */
    public function hitRateLimit(string $key, int $decayMinutes = 1): void
    {
        RateLimiter::hit($key, $decayMinutes * 60);
    }

    /**
     * Get rate limit remaining attempts
     */
    public function getRateLimitRemaining(string $key): int
    {
        return RateLimiter::remaining($key, 60);
    }

    /**
     * Generate signed URL for document access
     */
    public function generateDocumentAccessUrl(int $documentId, int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'documents.download',
            now()->addMinutes($expirationMinutes),
            ['document' => $documentId]
        );
    }

    /**
     * Generate secure token for sensitive operations
     */
    public function generateSecureToken(int $length = 32): string
    {
        return Str::random($length);
    }

    /**
     * Validate secure token
     */
    public function validateSecureToken(string $token, string $storedToken): bool
    {
        return hash_equals($storedToken, $token);
    }

    /**
     * Get security statistics
     */
    public function getSecurityStats(): array
    {
        return Cache::remember('security_stats', 300, function () {
            try {
                $recentEvents = collect();
                try {
                    $recentEvents = $this->auditLog->getRecentLogs(24)->filter(function ($log) {
                        return in_array($log->action, ['failed_login_attempt', 'ip_blocked', 'ip_unblocked']);
                    })->values();
                } catch (Exception $e) {
                    Log::error('Failed to get recent security events', ['error' => $e->getMessage()]);
                }
                
                return [
                    'failed_login_attempts_today' => LoginAttempt::failed()
                        ->whereDate('attempted_at', today())
                        ->count(),
                    'blocked_ips_count' => IpBlock::active()->count(),
                    'recent_security_events' => $recentEvents,
                    'rate_limits_exceeded' => $this->getRateLimitViolations(),
                ];
            } catch (Exception $e) {
                Log::error('Failed to get security stats', ['error' => $e->getMessage()]);
                return [
                    'failed_login_attempts_today' => 0,
                    'blocked_ips_count' => 0,
                    'recent_security_events' => collect(),
                    'rate_limits_exceeded' => 0,
                ];
            }
        });
    }

    /**
     * Get rate limit violations (simplified)
     */
    private function getRateLimitViolations(): int
    {
        // This would need to be implemented based on your rate limiting setup
        return 0;
    }

    /**
     * Clean up old security data
     */
    public function cleanupSecurityData(): array
    {
        $loginAttemptsDeleted = LoginAttempt::cleanupOldAttempts();
        $ipBlocksDeleted = IpBlock::cleanupExpiredBlocks();

        return [
            'login_attempts_cleaned' => $loginAttemptsDeleted,
            'ip_blocks_cleaned' => $ipBlocksDeleted,
        ];
    }

    /**
     * Check if IP is suspicious
     */
    public function isSuspiciousIp(string $ip): bool
    {
        // Check for multiple failed attempts from same IP
        $failedAttempts = LoginAttempt::byIp($ip)->failed()->recent(60)->count();

        // Check for rapid requests (would need request logging)
        // Check for unusual user agents, etc.

        return $failedAttempts > 10;
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(string $event, $user = null, $entity = null, array $metadata = []): void
    {
        $this->auditLog->log($event, $user, $entity ? get_class($entity) : null, $entity ? $entity->id : null, null, $metadata);
    }
}
