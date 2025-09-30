<?php

namespace App\Exceptions;

class ValidationException extends BaseException
{
    protected $httpStatusCode = 422;
    protected $logLevel = 'warning';

    protected function getDefaultMessage(): string
    {
        return 'Validation failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 2001;
    }
}