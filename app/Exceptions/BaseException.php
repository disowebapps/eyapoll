<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class BaseException extends Exception
{
    protected $context = [];
    protected $errorCode;
    protected $httpStatusCode = 400;
    protected $logLevel = 'error';

    public function __construct(string $message = "", array $context = [], int $code = 0, ?Exception $previous = null)
    {
        $this->context = $context;
        $this->errorCode = $code ?: $this->getDefaultErrorCode();

        parent::__construct($message ?: $this->getDefaultMessage(), $this->errorCode, $previous);

        $this->logException();
    }

    abstract protected function getDefaultMessage(): string;
    abstract protected function getDefaultErrorCode(): int;

    public function getContext(): array
    {
        return $this->context;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getLogLevel(): string
    {
        return $this->logLevel;
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->message,
                'type' => class_basename($this),
            ]
        ];

        // Add context in development/debug mode
        if (config('app.debug')) {
            $response['error']['context'] = $this->context;
            $response['error']['file'] = $this->getFile();
            $response['error']['line'] = $this->getLine();
        }

        return response()->json($response, $this->httpStatusCode);
    }

    protected function logException(): void
    {
        $logData = [
            'exception' => class_basename($this),
            'message' => $this->message,
            'code' => $this->errorCode,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'context' => $this->context,
        ];

        if ($this->getPrevious()) {
            $previous = $this->getPrevious();
            $logData['previous'] = [
                'message' => $previous->getMessage(),
                'file' => $previous->getFile(),
                'line' => $previous->getLine(),
            ];
        }

        Log::log($this->logLevel, 'Exception occurred: ' . class_basename($this), $logData);
    }
}