<?php

namespace App\Exceptions;

class DatabaseException extends BaseException
{
    protected $httpStatusCode = 500;

    protected function getDefaultMessage(): string
    {
        return 'Database operation failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 9001;
    }
}