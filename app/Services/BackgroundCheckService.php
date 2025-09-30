<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BackgroundCheckService
{
    private array $providers;

    public function __construct()
    {
        $this->providers = config('services.background_check', []);
    }

    /**
     * Perform background check for user
     */
    public function performBackgroundCheck(User $user, string $provider = 'default'): array
    {
        try {
            $providerConfig = $this->getProviderConfig($provider);

            if (!$providerConfig) {
                return $this->failCheck('Background check provider not configured');
            }

            // Check if already completed recently
            if ($user->background_check_completed &&
                $user->background_check_at &&
                $user->background_check_at->diffInDays(now()) < 90) { // 90 days validity

                return [
                    'completed' => true,
                    'previously_completed' => true,
                    'last_check' => $user->background_check_at,
                    'results' => json_decode($user->background_check_results, true),
                    'provider' => $user->background_check_provider
                ];
            }

            // Prepare user data for background check
            $checkData = $this->prepareCheckData($user);

            // Perform the check
            $result = $this->executeCheck($providerConfig, $checkData);

            if (!$result['success']) {
                return $this->failCheck('Background check failed: ' . $result['error']);
            }

            // Process and store results
            $processedResult = $this->processCheckResults($result['data'], $provider);

            // Update user record
            $user->update([
                'background_check_completed' => true,
                'background_check_at' => now(),
                'background_check_results' => json_encode($processedResult),
                'background_check_provider' => $provider,
                'background_check_status' => $processedResult['status']
            ]);

            return [
                'completed' => true,
                'status' => $processedResult['status'],
                'results' => $processedResult,
                'provider' => $provider,
                'checked_at' => now()
            ];

        } catch (\Exception $e) {
            Log::error('Background check failed', [
                'user_id' => $user->id,
                'provider' => $provider,
                'error' => $e->getMessage()
            ]);

            return $this->failCheck('Background check failed: ' . $e->getMessage());
        }
    }

    /**
     * Get provider configuration
     */
    private function getProviderConfig(string $provider): ?array
    {
        return $this->providers[$provider] ?? $this->providers['default'] ?? null;
    }

    /**
     * Prepare user data for background check
     */
    private function prepareCheckData(User $user): array
    {
        return [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone_number,
            'date_of_birth' => $user->date_of_birth ?? null, // Assuming this field exists
            'ssn' => $user->ssn ?? null, // Assuming this field exists
            'address' => [
                'street' => $user->address_street ?? null,
                'city' => $user->address_city ?? null,
                'state' => $user->address_state ?? null,
                'zip' => $user->address_zip ?? null,
                'country' => $user->address_country ?? null,
            ]
        ];
    }

    /**
     * Execute background check with provider
     */
    private function executeCheck(array $providerConfig, array $checkData): array
    {
        $providerType = $providerConfig['type'] ?? 'mock';

        switch ($providerType) {
            case 'checkr':
                return $this->checkCheckr($providerConfig, $checkData);
            case 'sterling':
                return $this->checkSterling($providerConfig, $checkData);
            case 'mock':
            default:
                return $this->mockCheck($checkData);
        }
    }

    /**
     * CheckR API integration
     */
    private function checkCheckr(array $config, array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($config['api_key'] . ':'),
                'Content-Type' => 'application/json',
            ])->post($config['base_url'] . '/v1/candidates', [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                // Add other required fields
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'CheckR API error: ' . $response->status()
                ];
            }

            return [
                'success' => true,
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'CheckR request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sterling API integration
     */
    private function checkSterling(array $config, array $data): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $config['api_key'],
                'Content-Type' => 'application/json',
            ])->post($config['base_url'] . '/api/v1/orders', [
                'package_type' => 'basic_background_check',
                'candidate' => [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ]
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Sterling API error: ' . $response->status()
                ];
            }

            return [
                'success' => true,
                'data' => $response->json()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Sterling request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Mock background check for development/testing
     */
    private function mockCheck(array $data): array
    {
        // Simulate API delay
        sleep(1);

        // Generate mock results based on user data
        $mockResults = $this->generateMockResults($data);

        return [
            'success' => true,
            'data' => $mockResults
        ];
    }

    /**
     * Generate mock background check results
     */
    private function generateMockResults(array $data): array
    {
        // Simple mock logic - in production, this would come from real API
        $hasIssues = rand(1, 10) > 8; // 20% chance of issues

        return [
            'candidate_id' => 'mock_' . uniqid(),
            'status' => $hasIssues ? 'consider' : 'clear',
            'completed_at' => now()->toISOString(),
            'checks' => [
                'ssn_verification' => [
                    'status' => 'clear',
                    'details' => 'SSN verified successfully'
                ],
                'criminal_history' => [
                    'status' => $hasIssues ? 'consider' : 'clear',
                    'details' => $hasIssues ?
                        'Minor criminal record found - requires review' :
                        'No criminal history found'
                ],
                'address_history' => [
                    'status' => 'clear',
                    'details' => 'Address history verified'
                ],
                'employment_verification' => [
                    'status' => 'clear',
                    'details' => 'Employment history verified'
                ]
            ],
            'report_url' => 'https://mock-provider.com/report/' . uniqid(),
            'mock' => true
        ];
    }

    /**
     * Process check results into standardized format
     */
    private function processCheckResults(array $apiResult, string $provider): array
    {
        $processed = [
            'provider' => $provider,
            'raw_response' => $apiResult,
            'processed_at' => now(),
            'status' => 'unknown',
            'flags' => [],
            'recommendations' => [],
            'summary' => ''
        ];

        // Process based on provider
        switch ($provider) {
            case 'checkr':
                $processed = array_merge($processed, $this->processCheckrResults($apiResult));
                break;
            case 'sterling':
                $processed = array_merge($processed, $this->processSterlingResults($apiResult));
                break;
            default:
                $processed = array_merge($processed, $this->processMockResults($apiResult));
        }

        return $processed;
    }

    /**
     * Process CheckR results
     */
    private function processCheckrResults(array $result): array
    {
        // CheckR specific processing logic
        $status = $result['status'] ?? 'unknown';
        $flags = [];
        $recommendations = [];

        if ($status === 'clear') {
            $status = 'clear';
        } elseif (in_array($status, ['consider', 'suspended'])) {
            $status = 'consider';
            $flags[] = 'Background check requires manual review';
            $recommendations[] = 'Contact candidate for additional information';
        }

        return [
            'status' => $status,
            'flags' => $flags,
            'recommendations' => $recommendations,
            'summary' => 'CheckR background check completed'
        ];
    }

    /**
     * Process Sterling results
     */
    private function processSterlingResults(array $result): array
    {
        // Sterling specific processing logic
        $status = $result['status'] ?? 'unknown';
        $flags = [];
        $recommendations = [];

        // Map Sterling statuses to our format
        $statusMap = [
            'completed' => 'clear',
            'in_progress' => 'pending',
            'failed' => 'failed'
        ];

        $status = $statusMap[$status] ?? 'unknown';

        return [
            'status' => $status,
            'flags' => $flags,
            'recommendations' => $recommendations,
            'summary' => 'Sterling background check completed'
        ];
    }

    /**
     * Process mock results
     */
    private function processMockResults(array $result): array
    {
        return [
            'status' => $result['status'] ?? 'unknown',
            'flags' => $result['checks'] ? array_filter($result['checks'], fn($check) => $check['status'] === 'consider') : [],
            'recommendations' => ['Mock background check - review manually'],
            'summary' => 'Mock background check completed for testing'
        ];
    }

    /**
     * Return failed check result
     */
    private function failCheck(string $reason): array
    {
        return [
            'completed' => false,
            'error' => $reason
        ];
    }

    /**
     * Get background check status for user
     */
    public function getCheckStatus(User $user): array
    {
        return [
            'completed' => $user->background_check_completed,
            'status' => $user->background_check_status,
            'provider' => $user->background_check_provider,
            'completed_at' => $user->background_check_at,
            'results' => $user->background_check_results ? json_decode($user->background_check_results, true) : null
        ];
    }

    /**
     * Check if background check is required for user
     */
    public function isCheckRequired(User $user): bool
    {
        // Logic to determine if background check is required
        // Based on user role, risk level, jurisdiction, etc.

        if ($user->role === 'candidate') {
            return true; // Candidates always require background checks
        }

        if ($user->risk_level === 'high' || $user->risk_level === 'critical') {
            return true; // High-risk users require checks
        }

        return false;
    }

    /**
     * Get available providers
     */
    public function getAvailableProviders(): array
    {
        return array_keys($this->providers);
    }
}