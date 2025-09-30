<?php

namespace App\Exceptions;

class ConfigurationException extends BaseException
{
    protected $httpStatusCode = 500;

    protected function getDefaultMessage(): string
    {
        return 'Configuration error';
    }

    protected function getDefaultErrorCode(): int
    {
        return 11001;
    }
}