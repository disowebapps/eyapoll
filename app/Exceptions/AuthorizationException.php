<?php

namespace App\Exceptions;

class AuthorizationException extends BaseException
{
    protected $httpStatusCode = 403;

    protected function getDefaultMessage(): string
    {
        return 'Access denied';
    }

    protected function getDefaultErrorCode(): int
    {
        return 1002;
    }
}