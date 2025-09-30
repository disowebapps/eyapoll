<?php

namespace App\Exceptions;

class CryptographicException extends BaseException
{
    protected $httpStatusCode = 500;

    protected function getDefaultMessage(): string
    {
        return 'Cryptographic operation failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 7001;
    }
}