<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\RetryService;
use Exception;

class RetryServiceTest extends TestCase
{
    public function test_retry_executes_successfully_on_first_attempt()
    {
        $callable = function () {
            return 'success';
        };

        $result = RetryService::retry($callable, 3);

        $this->assertEquals('success', $result);
    }

    public function test_retry_succeeds_after_failures()
    {
        $attempts = 0;

        $callable = function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new \Illuminate\Database\QueryException('connection', 'SELECT 1', [], new Exception('Temporary failure'));
            }
            return 'success';
        };

        $result = RetryService::retry($callable, 3);

        $this->assertEquals('success', $result);
        $this->assertEquals(3, $attempts);
    }

    public function test_retry_fails_after_max_attempts()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Persistent failure');

        $callable = function () {
            throw new Exception('Persistent failure');
        };

        RetryService::retry($callable, 2);
    }

    public function test_retry_database_handles_database_exceptions()
    {
        $callable = function () {
            static $attempts = 0;
            $attempts++;
            if ($attempts < 2) {
                throw new \Illuminate\Database\QueryException('connection', 'SELECT 1', [], new Exception('Connection lost'));
            }
            return 'success';
        };

        $result = RetryService::retryDatabase($callable, 2);

        $this->assertEquals('success', $result);
    }

    public function test_retry_http_handles_connection_exceptions()
    {
        $callable = function () {
            static $attempts = 0;
            $attempts++;
            if ($attempts < 2) {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            }
            return 'success';
        };

        $result = RetryService::retryHttp($callable, 2);

        $this->assertEquals('success', $result);
    }

    public function test_retry_external_service_handles_custom_exceptions()
    {
        $callable = function () {
            static $attempts = 0;
            $attempts++;
            if ($attempts < 2) {
                throw new \App\Exceptions\ExternalServiceException('Service unavailable');
            }
            return 'success';
        };

        $result = RetryService::retryExternalService($callable, 2);

        $this->assertEquals('success', $result);
    }

    public function test_retry_does_not_retry_non_retryable_exceptions()
    {
        $this->expectException(\InvalidArgumentException::class);

        $callable = function () {
            throw new \InvalidArgumentException('Invalid argument');
        };

        RetryService::retry($callable, 3);
    }

    public function test_retry_uses_exponential_backoff()
    {
        $startTime = microtime(true);

        $callable = function () use ($startTime) {
            static $attempts = 0;
            $attempts++;
            if ($attempts < 3) {
                throw new \Illuminate\Database\QueryException('connection', 'SELECT 1', [], new Exception('Retryable failure'));
            }
            return microtime(true) - $startTime;
        };

        $result = RetryService::retry($callable, 3, 50); // 50ms base delay

        // Should have taken at least some time due to delays
        $this->assertGreaterThan(0.1, $result); // At least 100ms total delay
    }
}