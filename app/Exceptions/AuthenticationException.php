<?php

namespace App\Exceptions;

class AuthenticationException extends BaseException
{
    protected $httpStatusCode = 401;

    protected function getDefaultMessage(): string
    {
        return 'Authentication failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 1001;
    }
}