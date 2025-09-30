<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use ReflectionMethod;

class GenerateApiDocumentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-docs {--format=openapi : The output format (openapi, postman)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate API documentation from routes and controllers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $format = $this->option('format');

        $this->info("Generating API documentation in {$format} format...");

        $routes = $this->getApiRoutes();
        $documentation = $this->generateDocumentation($routes, $format);

        $this->saveDocumentation($documentation, $format);

        $this->info('API documentation generated successfully!');
        $this->info("File saved to: storage/app/api-docs/api-documentation.{$this->getFileExtension($format)}");

        return Command::SUCCESS;
    }

    /**
     * Get all API routes
     */
    private function getApiRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'api/')) {
                $routes[] = [
                    'uri' => $route->uri(),
                    'methods' => $route->methods(),
                    'action' => $route->getAction(),
                    'middleware' => $route->middleware(),
                ];
            }
        }

        return $routes;
    }

    /**
     * Generate documentation based on format
     */
    private function generateDocumentation(array $routes, string $format): array
    {
        switch ($format) {
            case 'openapi':
                return $this->generateOpenApiDocumentation($routes);
            case 'postman':
                return $this->generatePostmanDocumentation($routes);
            default:
                throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    /**
     * Generate OpenAPI 3.0 documentation
     */
    private function generateOpenApiDocumentation(array $routes): array
    {
        $paths = [];

        foreach ($routes as $route) {
            $path = '/' . $route['uri'];
            $methods = array_map('strtolower', $route['methods']);

            foreach ($methods as $method) {
                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }

                $paths[$path][$method] = [
                    'summary' => $this->generateRouteSummary($route, $method),
                    'description' => $this->generateRouteDescription($route),
                    'tags' => $this->getRouteTags($route),
                    'security' => $this->getRouteSecurity($route),
                    'parameters' => $this->getRouteParameters($route, $method),
                    'requestBody' => $this->getRequestBody($route, $method),
                    'responses' => $this->getResponses($route, $method),
                ];
            }
        }

        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'AyaPoll API',
                'description' => 'API for the AyaPoll voting system',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'AyaPoll Support',
                    'email' => 'support@ayapoll.com',
                ],
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'Production server',
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
                'schemas' => $this->getCommonSchemas(),
            ],
            'paths' => $paths,
        ];
    }

    /**
     * Generate Postman collection documentation
     */
    private function generatePostmanDocumentation(array $routes): array
    {
        $items = [];

        foreach ($routes as $route) {
            $methods = array_map('strtolower', $route['methods']);

            foreach ($methods as $method) {
                $items[] = [
                    'name' => $this->generateRouteSummary($route, $method),
                    'request' => [
                        'method' => strtoupper($method),
                        'header' => [
                            [
                                'key' => 'Accept',
                                'value' => 'application/json',
                            ],
                            [
                                'key' => 'Content-Type',
                                'value' => 'application/json',
                            ],
                        ],
                        'url' => [
                            'raw' => '{{base_url}}/' . $route['uri'],
                            'host' => ['{{base_url}}'],
                            'path' => explode('/', $route['uri']),
                        ],
                    ],
                ];
            }
        }

        return [
            'info' => [
                'name' => 'AyaPoll API',
                'description' => 'API collection for AyaPoll voting system',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'variable' => [
                [
                    'key' => 'base_url',
                    'value' => config('app.url') . '/api',
                ],
            ],
            'item' => $items,
        ];
    }

    /**
     * Generate route summary
     */
    private function generateRouteSummary(array $route, string $method): string
    {
        $action = $route['action'];
        $controller = $action['controller'] ?? '';

        if ($controller) {
            $parts = explode('@', $controller);
            $methodName = $parts[1] ?? '';
            return ucfirst($method) . ' ' . str_replace('_', ' ', $methodName);
        }

        return ucfirst($method) . ' ' . str_replace(['api/', '/'], ['', ' '], $route['uri']);
    }

    /**
     * Generate route description
     */
    private function generateRouteDescription(array $route): string
    {
        $action = $route['action'];
        $controller = $action['controller'] ?? '';

        if ($controller) {
            try {
                $parts = explode('@', $controller);
                $className = $parts[0];
                $methodName = $parts[1];

                $reflection = new ReflectionMethod($className, $methodName);
                $docComment = $reflection->getDocComment();

                if ($docComment) {
                    // Extract description from PHPDoc
                    preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\n/', $docComment, $matches);
                    return $matches[1] ?? 'API endpoint';
                }
            } catch (\Exception $e) {
                // Ignore reflection errors
            }
        }

        return 'API endpoint for ' . $route['uri'];
    }

    /**
     * Get route tags
     */
    private function getRouteTags(array $route): array
    {
        $uri = $route['uri'];

        if (str_contains($uri, 'admin')) {
            return ['Admin'];
        } elseif (str_contains($uri, 'voting')) {
            return ['Voting'];
        } elseif (str_contains($uri, 'election')) {
            return ['Elections'];
        } elseif (str_contains($uri, 'user')) {
            return ['Users'];
        }

        return ['General'];
    }

    /**
     * Get route security requirements
     */
    private function getRouteSecurity(array $route): array
    {
        $middleware = $route['middleware'];

        if (in_array('auth:sanctum', $middleware) || in_array('auth', $middleware)) {
            return [['bearerAuth' => []]];
        }

        return [];
    }

    /**
     * Get route parameters
     */
    private function getRouteParameters(array $route, string $method): array
    {
        $parameters = [];
        $uri = $route['uri'];

        // Extract path parameters
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);

        foreach ($matches[1] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => 'string',
                ],
                'description' => ucfirst(str_replace('_', ' ', $param)),
            ];
        }

        // Add query parameters for GET requests
        if ($method === 'get') {
            $parameters[] = [
                'name' => 'page',
                'in' => 'query',
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'description' => 'Page number for pagination',
            ];

            $parameters[] = [
                'name' => 'per_page',
                'in' => 'query',
                'schema' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                ],
                'description' => 'Number of items per page',
            ];
        }

        return $parameters;
    }

    /**
     * Get request body
     */
    private function getRequestBody(array $route, string $method): ?array
    {
        if (in_array($method, ['post', 'put', 'patch'])) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $this->getRequestBodySchema($route),
                        ],
                    ],
                ],
            ];
        }

        return null;
    }

    /**
     * Get request body schema
     */
    private function getRequestBodySchema(array $route): array
    {
        $uri = $route['uri'];

        // Define schemas based on common patterns
        if (str_contains($uri, 'voting')) {
            return [
                'election_id' => ['type' => 'integer'],
                'selections' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                    ],
                ],
            ];
        } elseif (str_contains($uri, 'election') && str_contains($route['methods'], 'POST')) {
            return [
                'title' => ['type' => 'string'],
                'description' => ['type' => 'string'],
                'start_date' => ['type' => 'string', 'format' => 'date-time'],
                'end_date' => ['type' => 'string', 'format' => 'date-time'],
            ];
        }

        return [
            'data' => ['type' => 'object', 'description' => 'Request data'],
        ];
    }

    /**
     * Get responses
     */
    private function getResponses(array $route, string $method): array
    {
        $responses = [
            '200' => [
                'description' => 'Successful operation',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean', 'example' => true],
                                'data' => ['type' => 'object'],
                                'message' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
            '401' => [
                'description' => 'Unauthorized',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error',
                        ],
                    ],
                ],
            ],
            '422' => [
                'description' => 'Validation error',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/ValidationError',
                        ],
                    ],
                ],
            ],
            '500' => [
                'description' => 'Internal server error',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error',
                        ],
                    ],
                ],
            ],
        ];

        // Add 201 for POST requests
        if ($method === 'post') {
            $responses['201'] = [
                'description' => 'Resource created',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'success' => ['type' => 'boolean', 'example' => true],
                                'data' => ['type' => 'object'],
                                'message' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return $responses;
    }

    /**
     * Get common schemas
     */
    private function getCommonSchemas(): array
    {
        return [
            'Error' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'error' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'integer'],
                            'message' => ['type' => 'string'],
                            'type' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'error' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'integer', 'example' => 2001],
                            'message' => ['type' => 'string', 'example' => 'Validation failed'],
                            'type' => ['type' => 'string', 'example' => 'ValidationException'],
                        ],
                    ],
                    'errors' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                    ],
                ],
            ],
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'uuid' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'first_name' => ['type' => 'string'],
                    'last_name' => ['type' => 'string'],
                    'full_name' => ['type' => 'string'],
                    'role' => ['type' => 'string'],
                    'status' => ['type' => 'string'],
                ],
            ],
        ];
    }

    /**
     * Save documentation to file
     */
    private function saveDocumentation(array $documentation, string $format): void
    {
        $filename = "api-documentation.{$this->getFileExtension($format)}";
        $path = "api-docs/{$filename}";

        Storage::disk('local')->put($path, json_encode($documentation, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Get file extension for format
     */
    private function getFileExtension(string $format): string
    {
        return $format === 'postman' ? 'json' : 'json';
    }
}