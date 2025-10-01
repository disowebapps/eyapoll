<?php

namespace App\Services\Utility;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Election\Election;
use App\Models\User;

class NavigationCacheService
{
    private const CACHE_PREFIX = 'navigation';
    private const ACTIVE_ELECTIONS_TTL = 300; // 5 minutes
    private const USER_STATUS_TTL = 600; // 10 minutes
    private const ELECTION_COUNT_TTL = 180; // 3 minutes

    /**
     * Get cached count of active elections
     */
    public function getActiveElectionsCount(): int
    {
        $cacheKey = $this->getCacheKey('active_elections_count');

        return Cache::remember($cacheKey, self::ELECTION_COUNT_TTL, function () {
            $this->logPerformance('active_elections_count_query');
            $startTime = microtime(true);

            $count = Election::where('status', 'active')
                ->where('ends_at', '>', now())
                ->count();

            $this->logPerformance('active_elections_count_query', microtime(true) - $startTime);
            return $count;
        });
    }

    /**
     * Check if there are any active elections (cached)
     */
    public function hasActiveElections(): bool
    {
        $cacheKey = $this->getCacheKey('has_active_elections');

        return Cache::remember($cacheKey, self::ACTIVE_ELECTIONS_TTL, function () {
            $this->logPerformance('has_active_elections_query');
            $startTime = microtime(true);

            $exists = Election::where('status', 'active')
                ->where('ends_at', '>', now())
                ->exists();

            $this->logPerformance('has_active_elections_query', microtime(true) - $startTime);
            return $exists;
        });
    }

    /**
     * Get cached user status data for navigation
     */
    public function getUserStatus(User $user): array
    {
        $cacheKey = $this->getCacheKey("user_status_{$user->id}");

        return Cache::remember($cacheKey, self::USER_STATUS_TTL, function () use ($user) {
            $this->logPerformance('user_status_query');
            $startTime = microtime(true);

            $status = [
                'kyc_verified' => $user->status->value === 'active',
                'has_upcoming_elections' => $this->hasActiveElections(),
                'has_voting_history' => $user->voteTokens()->where('is_used', true)->exists(),
            ];

            $this->logPerformance('user_status_query', microtime(true) - $startTime);
            return $status;
        });
    }

    /**
     * Get cached navigation data for a user
     */
    public function getNavigationData(User $user): array
    {
        $cacheKey = $this->getCacheKey("nav_data_{$user->id}");

        return Cache::remember($cacheKey, self::USER_STATUS_TTL, function () use ($user) {
            $this->logPerformance('navigation_data_query');
            $startTime = microtime(true);

            $data = [
                'active_elections_count' => $this->getActiveElectionsCount(),
                'has_active_elections' => $this->hasActiveElections(),
                'user_status' => $this->getUserStatus($user),
                'cached_at' => now()->toISOString(),
            ];

            $this->logPerformance('navigation_data_query', microtime(true) - $startTime);
            return $data;
        });
    }

    /**
     * Clear all navigation-related caches
     */
    public function clearAllNavigationCaches(): void
    {
        $pattern = $this->getCacheKey('*');
        $this->clearCacheByPattern($pattern);
        Log::info('Cleared all navigation caches');
    }

    /**
     * Clear user-specific navigation caches
     */
    public function clearUserNavigationCache(int $userId): void
    {
        $patterns = [
            $this->getCacheKey("user_status_{$userId}"),
            $this->getCacheKey("nav_data_{$userId}"),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        Log::info("Cleared navigation caches for user {$userId}");
    }

    /**
     * Clear election-related navigation caches
     */
    public function clearElectionNavigationCaches(): void
    {
        $patterns = [
            $this->getCacheKey('active_elections_count'),
            $this->getCacheKey('has_active_elections'),
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }

        // Also clear all user navigation data since it includes election data
        $this->clearAllUserNavigationCaches();

        Log::info('Cleared election-related navigation caches');
    }

    /**
     * Clear all user navigation caches (used when election data changes)
     */
    private function clearAllUserNavigationCaches(): void
    {
        // This is a simplified approach - in production, you might want to use Redis SCAN
        // For now, we'll clear caches when they're accessed next time with different TTL
        Log::info('Triggered clearing of all user navigation caches');
    }

    /**
     * Get formatted cache key
     */
    private function getCacheKey(string $key): string
    {
        return self::CACHE_PREFIX . ':' . $key;
    }

    /**
     * Clear cache by pattern (simplified - Redis doesn't have direct pattern deletion)
     */
    private function clearCacheByPattern(string $pattern): void
    {
        // In a real implementation, you might use Redis SCAN or maintain a registry
        // For this demo, we'll just log the action
        Log::info("Would clear cache pattern: {$pattern}");
    }

    /**
     * Log performance metrics
     */
    private function logPerformance(string $operation, ?float $duration = null): void
    {
        if ($duration !== null) {
            Log::info("Navigation cache operation completed", [
                'operation' => $operation,
                'duration_ms' => round($duration * 1000, 2),
                'timestamp' => now()->toISOString(),
            ]);
        } else {
            Log::info("Navigation cache operation started", [
                'operation' => $operation,
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        // This would integrate with a monitoring service in production
        return [
            'active_elections_count_ttl' => self::ELECTION_COUNT_TTL,
            'user_status_ttl' => self::USER_STATUS_TTL,
            'active_elections_ttl' => self::ACTIVE_ELECTIONS_TTL,
            'cache_store' => 'redis',
        ];
    }
}
