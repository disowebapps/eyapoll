<?php

namespace App\Exceptions;

class FileUploadException extends BaseException
{
    protected $httpStatusCode = 400;

    protected function getDefaultMessage(): string
    {
        return 'File upload failed';
    }

    protected function getDefaultErrorCode(): int
    {
        return 8001;
    }
}