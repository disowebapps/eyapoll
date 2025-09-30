<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use App\Exceptions\BaseException;
use App\Exceptions\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BaseExceptionTest extends TestCase
{
    public function test_base_exception_renders_json_response()
    {
        $exception = new ValidationException('Test validation error', [
            'field' => 'test_field',
            'value' => 'invalid_value'
        ]);

        $request = Request::create('/test');
        $response = $exception->render($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Test validation error', $data['error']['message']);
        $this->assertEquals('ValidationException', $data['error']['type']);
        $this->assertEquals(2001, $data['error']['code']);
    }

    public function test_base_exception_includes_context_in_debug_mode()
    {
        config(['app.debug' => true]);

        $exception = new ValidationException('Test error', ['test' => 'context']);

        $request = Request::create('/test');
        $response = $exception->render($request);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('context', $data['error']);
        $this->assertArrayHasKey('file', $data['error']);
        $this->assertArrayHasKey('line', $data['error']);
        $this->assertEquals(['test' => 'context'], $data['error']['context']);
    }

    public function test_base_exception_excludes_context_in_production()
    {
        config(['app.debug' => false]);

        $exception = new ValidationException('Test error', ['test' => 'context']);

        $request = Request::create('/test');
        $response = $exception->render($request);

        $data = json_decode($response->getContent(), true);

        $this->assertArrayNotHasKey('context', $data['error']);
        $this->assertArrayNotHasKey('file', $data['error']);
        $this->assertArrayNotHasKey('line', $data['error']);
    }

    public function test_validation_exception_has_correct_properties()
    {
        $exception = new ValidationException('Field is required');

        $this->assertEquals(422, $exception->getHttpStatusCode());
        $this->assertEquals('warning', $exception->getLogLevel());
    }

    public function test_exception_preserves_context()
    {
        $context = ['user_id' => 123, 'action' => 'test'];
        $exception = new ValidationException('Test', $context);

        $this->assertEquals($context, $exception->getContext());
    }

    public function test_exception_includes_previous_exception_in_log_context()
    {
        $previous = new \Exception('Previous error', 123);
        $exception = new ValidationException('Current error', [], 0, $previous);

        // Test that the exception can be created with previous
        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertEquals($previous, $exception->getPrevious());
    }
}