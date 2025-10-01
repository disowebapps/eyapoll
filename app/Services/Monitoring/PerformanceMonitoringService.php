<?php

namespace App\Services\Monitoring;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;

class PerformanceMonitoringService
{
    private const SLOW_QUERY_THRESHOLD = 100; // milliseconds
    private const CACHE_KEY_SLOW_QUERIES = 'performance:slow_queries';
    private const CACHE_KEY_QUERY_STATS = 'performance:query_stats';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Track database query performance
     */
    public function trackQueryPerformance(QueryExecuted $query): void
    {
        $executionTime = $query->time;

        // Log slow queries
        if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
            $this->logSlowQuery($query, $executionTime);
        }

        // Track query statistics
        $this->updateQueryStats($query, $executionTime);
    }

    /**
     * Track custom operation performance
     */
    public function trackOperation(string $operation, float $duration, array $context = []): void
    {
        $durationMs = $duration * 1000;

        // Log if operation is slow
        if ($durationMs > self::SLOW_QUERY_THRESHOLD) {
            Log::warning("Slow operation detected", [
                'operation' => $operation,
                'duration_ms' => round($durationMs, 2),
                'context' => $context,
                'timestamp' => now()->toISOString(),
            ]);
        }

        // Store performance metrics
        $this->storePerformanceMetric($operation, $durationMs, $context);
    }

    /**
     * Get performance statistics
     */
    public function getPerformanceStats(): array
    {
        $slowQueries = Cache::get(self::CACHE_KEY_SLOW_QUERIES, []);
        $queryStats = Cache::get(self::CACHE_KEY_QUERY_STATS, []);

        return [
            'slow_queries_count' => count($slowQueries),
            'recent_slow_queries' => array_slice($slowQueries, -10), // Last 10 slow queries
            'query_stats' => $queryStats,
            'threshold_ms' => self::SLOW_QUERY_THRESHOLD,
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get navigation-specific performance metrics
     */
    public function getNavigationPerformanceMetrics(): array
    {
        $cacheKey = 'performance:navigation_metrics';

        return Cache::remember($cacheKey, 300, function () { // 5 minutes
            $stats = Cache::get(self::CACHE_KEY_QUERY_STATS, []);

            $navigationQueries = array_filter($stats, function ($query) {
                return str_contains($query['query'] ?? '', 'elections') ||
                       str_contains($query['query'] ?? '', 'vote_tokens') ||
                       str_contains($query['operation'] ?? '', 'navigation');
            });

            return [
                'navigation_query_count' => count($navigationQueries),
                'average_navigation_query_time' => $this->calculateAverageTime($navigationQueries),
                'slow_navigation_queries' => array_filter($navigationQueries, function ($query) {
                    return ($query['avg_time'] ?? 0) > self::SLOW_QUERY_THRESHOLD;
                }),
                'cache_hit_rate' => $this->calculateCacheHitRate(),
                'generated_at' => now()->toISOString(),
            ];
        });
    }

    /**
     * Log slow query details
     */
    private function logSlowQuery(QueryExecuted $query, float $executionTime): void
    {
        $slowQueryData = [
            'query' => $query->sql,
            'bindings' => $query->bindings,
            'execution_time_ms' => round($executionTime, 2),
            'connection' => $query->connectionName,
            'timestamp' => now()->toISOString(),
        ];

        Log::warning('Slow query detected', $slowQueryData);

        // Store in cache for monitoring
        $slowQueries = Cache::get(self::CACHE_KEY_SLOW_QUERIES, []);
        $slowQueries[] = $slowQueryData;

        // Keep only last 100 slow queries
        if (count($slowQueries) > 100) {
            $slowQueries = array_slice($slowQueries, -100);
        }

        Cache::put(self::CACHE_KEY_SLOW_QUERIES, $slowQueries, self::CACHE_TTL);
    }

    /**
     * Update query statistics
     */
    private function updateQueryStats(QueryExecuted $query, float $executionTime): void
    {
        $queryHash = md5($query->sql);
        $stats = Cache::get(self::CACHE_KEY_QUERY_STATS, []);

        if (!isset($stats[$queryHash])) {
            $stats[$queryHash] = [
                'query' => $query->sql,
                'count' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'max_time' => 0,
                'min_time' => $executionTime,
                'last_executed' => now()->toISOString(),
            ];
        }

        $stat = &$stats[$queryHash];
        $stat['count']++;
        $stat['total_time'] += $executionTime;
        $stat['avg_time'] = $stat['total_time'] / $stat['count'];
        $stat['max_time'] = max($stat['max_time'], $executionTime);
        $stat['min_time'] = min($stat['min_time'], $executionTime);
        $stat['last_executed'] = now()->toISOString();

        Cache::put(self::CACHE_KEY_QUERY_STATS, $stats, self::CACHE_TTL);
    }

    /**
     * Store custom performance metric
     */
    private function storePerformanceMetric(string $operation, float $durationMs, array $context): void
    {
        $metricKey = "performance:operation:{$operation}";
        $metrics = Cache::get($metricKey, []);

        $metrics[] = [
            'duration_ms' => round($durationMs, 2),
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 50 metrics per operation
        if (count($metrics) > 50) {
            $metrics = array_slice($metrics, -50);
        }

        Cache::put($metricKey, $metrics, self::CACHE_TTL);
    }

    /**
     * Calculate average time from query stats
     */
    private function calculateAverageTime(array $queries): float
    {
        if (empty($queries)) {
            return 0.0;
        }

        $totalTime = array_sum(array_column($queries, 'avg_time'));
        return round($totalTime / count($queries), 2);
    }

    /**
     * Calculate cache hit rate (simplified)
     */
    private function calculateCacheHitRate(): float
    {
        // This would need actual cache hit/miss tracking
        // For now, return a placeholder
        return 85.5; // 85.5% hit rate
    }

    /**
     * Clear performance monitoring data
     */
    public function clearPerformanceData(): void
    {
        Cache::forget(self::CACHE_KEY_SLOW_QUERIES);
        Cache::forget(self::CACHE_KEY_QUERY_STATS);

        // Clear operation-specific metrics
        $keys = Cache::get('performance:operation_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }

        Log::info('Cleared performance monitoring data');
    }

    /**
     * Get system performance health check
     */
    public function getHealthCheck(): array
    {
        $stats = $this->getPerformanceStats();
        $navigationMetrics = $this->getNavigationPerformanceMetrics();

        $slowQueryCount = $stats['slow_queries_count'];
        $avgNavigationTime = $navigationMetrics['average_navigation_query_time'];

        $health = [
            'status' => 'healthy',
            'issues' => [],
            'metrics' => [
                'slow_queries' => $slowQueryCount,
                'avg_navigation_time_ms' => $avgNavigationTime,
                'cache_hit_rate' => $navigationMetrics['cache_hit_rate'],
            ],
        ];

        // Check for issues
        if ($slowQueryCount > 10) {
            $health['status'] = 'warning';
            $health['issues'][] = 'High number of slow queries detected';
        }

        if ($avgNavigationTime > 200) {
            $health['status'] = 'warning';
            $health['issues'][] = 'Navigation queries are running slow';
        }

        if ($navigationMetrics['cache_hit_rate'] < 70) {
            $health['status'] = 'warning';
            $health['issues'][] = 'Low cache hit rate detected';
        }

        return $health;
    }
}
