<?php

namespace App\Exceptions;

class ElectionException extends BaseException
{
    protected $httpStatusCode = 400;

    protected function getDefaultMessage(): string
    {
        return 'Election operation failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 4001;
    }
}