<?php

namespace App\Exceptions;

class ExternalServiceException extends BaseException
{
    protected $httpStatusCode = 502;

    protected function getDefaultMessage(): string
    {
        return 'External service unavailable';
    }

    protected function getDefaultErrorCode(): int
    {
        return 6001;
    }
}