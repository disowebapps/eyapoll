<?php

namespace App\Exceptions;

class BusinessLogicException extends BaseException
{
    protected $httpStatusCode = 400;

    protected function getDefaultMessage(): string
    {
        return 'Business logic violation';
    }

    protected function getDefaultErrorCode(): int
    {
        return 5001;
    }
}