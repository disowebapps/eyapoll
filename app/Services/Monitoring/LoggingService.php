<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class LoggingService
{
    /**
     * Log user authentication events
     */
    public static function logAuthEvent(string $event, array $context = []): void
    {
        $context = array_merge($context, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
        ]);

        Log::info("Auth: {$event}", $context);
    }

    /**
     * Log voting events
     */
    public static function logVotingEvent(string $event, int $electionId, array $context = []): void
    {
        $context = array_merge($context, [
            'election_id' => $electionId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::info("Voting: {$event}", $context);
    }

    /**
     * Log election management events
     */
    public static function logElectionEvent(string $event, int $electionId, array $context = []): void
    {
        $context = array_merge($context, [
            'election_id' => $electionId,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::info("Election: {$event}", $context);
    }

    /**
     * Log user management events
     */
    public static function logUserEvent(string $event, int $userId, array $context = []): void
    {
        $context = array_merge($context, [
            'target_user_id' => $userId,
            'timestamp' => now()->toISOString(),
            'admin_user_id' => auth()->id(),
        ]);

        Log::info("User: {$event}", $context);
    }

    /**
     * Log security events
     */
    public static function logSecurityEvent(string $event, array $context = []): void
    {
        $context = array_merge($context, [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::warning("Security: {$event}", $context);
    }

    /**
     * Log performance metrics
     */
    public static function logPerformance(string $operation, float $durationMs, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'duration_ms' => $durationMs,
            'timestamp' => now()->toISOString(),
        ]);

        $level = $durationMs > 1000 ? 'warning' : 'info';
        Log::log($level, "Performance: {$operation}", $context);
    }

    /**
     * Log database operations
     */
    public static function logDatabaseOperation(string $operation, string $table, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'table' => $table,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::info("Database: {$operation} on {$table}", $context);
    }

    /**
     * Log external API calls
     */
    public static function logApiCall(string $service, string $endpoint, int $statusCode, float $durationMs, array $context = []): void
    {
        $context = array_merge($context, [
            'service' => $service,
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'timestamp' => now()->toISOString(),
        ]);

        $level = $statusCode >= 400 ? 'warning' : 'info';
        Log::log($level, "API: {$service} {$endpoint}", $context);
    }

    /**
     * Log file operations
     */
    public static function logFileOperation(string $operation, string $filePath, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'file_path' => $filePath,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::info("File: {$operation} {$filePath}", $context);
    }

    /**
     * Log model changes
     */
    public static function logModelChange(Model $model, string $operation, array $changes = []): void
    {
        $context = [
            'model' => get_class($model),
            'model_id' => $model->getKey(),
            'operation' => $operation,
            'changes' => $changes,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ];

        Log::info("Model: {$operation} " . get_class($model) . ":{$model->getKey()}", $context);
    }

    /**
     * Log business rule violations
     */
    public static function logBusinessRuleViolation(string $rule, array $context = []): void
    {
        $context = array_merge($context, [
            'rule' => $rule,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
        ]);

        Log::warning("Business Rule Violation: {$rule}", $context);
    }

    /**
     * Log system health events
     */
    public static function logSystemHealth(string $component, string $status, array $metrics = []): void
    {
        $context = array_merge($metrics, [
            'component' => $component,
            'status' => $status,
            'timestamp' => now()->toISOString(),
        ]);

        $level = $status === 'healthy' ? 'info' : 'warning';
        Log::log($level, "Health: {$component} is {$status}", $context);
    }

    /**
     * Create a performance timer
     */
    public static function startTimer(): float
    {
        return microtime(true);
    }

    /**
     * End performance timer and log
     */
    public static function endTimer(float $startTime, string $operation, array $context = []): float
    {
        $duration = (microtime(true) - $startTime) * 1000;
        self::logPerformance($operation, $duration, $context);
        return $duration;
    }
}