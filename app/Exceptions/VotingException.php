<?php

namespace App\Exceptions;

class VotingException extends BaseException
{
    protected $httpStatusCode = 400;

    protected function getDefaultMessage(): string
    {
        return 'Voting operation failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 3001;
    }
}