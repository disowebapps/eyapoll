<?php

namespace App\Exceptions;

class RateLimitException extends BaseException
{
    protected $httpStatusCode = 429;
    protected $logLevel = 'warning';

    protected function getDefaultMessage(): string
    {
        return 'Too many requests';
    }

    protected function getDefaultErrorCode(): int
    {
        return 10001;
    }
}