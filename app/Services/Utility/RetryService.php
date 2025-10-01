<?php

namespace App\Services\Utility;

use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class RetryService
{
    /**
     * Execute a callable with retry logic
     */
    public static function retry(callable $callable, int $maxAttempts = 3, int $delayMs = 100, array $retryableExceptions = []): mixed
    {
        $attempts = 0;
        $lastException = null;

        while ($attempts < $maxAttempts) {
            try {
                $attempts++;
                return $callable();
            } catch (Throwable $e) {
                $lastException = $e;

                // Check if exception is retryable
                if (!self::isRetryableException($e, $retryableExceptions)) {
                    Log::warning('Non-retryable exception encountered', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'attempt' => $attempts,
                        'max_attempts' => $maxAttempts
                    ]);
                    throw $e;
                }

                if ($attempts >= $maxAttempts) {
                    Log::error('Max retry attempts exceeded', [
                        'exception' => get_class($e),
                        'message' => $e->getMessage(),
                        'attempts' => $attempts,
                        'max_attempts' => $maxAttempts
                    ]);
                    throw $e;
                }

                Log::info('Retrying operation after failure', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'attempt' => $attempts,
                    'max_attempts' => $maxAttempts,
                    'delay_ms' => $delayMs
                ]);

                // Exponential backoff
                usleep($delayMs * 1000 * $attempts);
            }
        }

        throw $lastException;
    }

    /**
     * Check if an exception is retryable
     */
    private static function isRetryableException(Throwable $e, array $retryableExceptions): bool
    {
        if (empty($retryableExceptions)) {
            // Default retryable exceptions
            $retryableExceptions = [
                \Illuminate\Database\QueryException::class,
                \PDOException::class,
                \Illuminate\Http\Client\ConnectionException::class,
                \Illuminate\Http\Client\RequestException::class,
            ];
        }

        foreach ($retryableExceptions as $exceptionClass) {
            if ($e instanceof $exceptionClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute database operation with retry
     */
    public static function retryDatabase(callable $callable, int $maxAttempts = 3): mixed
    {
        return self::retry($callable, $maxAttempts, 200, [
            \Illuminate\Database\QueryException::class,
            \PDOException::class,
            \Illuminate\Database\DeadlockException::class,
        ]);
    }

    /**
     * Execute HTTP request with retry
     */
    public static function retryHttp(callable $callable, int $maxAttempts = 3): mixed
    {
        return self::retry($callable, $maxAttempts, 500, [
            \Illuminate\Http\Client\ConnectionException::class,
            \Illuminate\Http\Client\RequestException::class,
        ]);
    }

    /**
     * Execute external service call with retry
     */
    public static function retryExternalService(callable $callable, int $maxAttempts = 2): mixed
    {
        return self::retry($callable, $maxAttempts, 1000, [
            \Illuminate\Http\Client\ConnectionException::class,
            \Illuminate\Http\Client\RequestException::class,
            \App\Exceptions\ExternalServiceException::class,
        ]);
    }
}
