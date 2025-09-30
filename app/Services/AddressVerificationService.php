<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AddressVerificationService
{
    private string $apiKey;
    private string $baseUrl = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key', env('GOOGLE_MAPS_API_KEY'));
    }

    /**
     * Verify user address using Google Maps API
     */
    public function verifyAddress(User $user, array $addressData): array
    {
        try {
            // Format address for API
            $formattedAddress = $this->formatAddressForAPI($addressData);

            // Check cache first
            $cacheKey = 'address_verification_' . md5($formattedAddress);
            $cachedResult = Cache::get($cacheKey);

            if ($cachedResult) {
                return $cachedResult;
            }

            // Geocode the address
            $geocodeResult = $this->geocodeAddress($formattedAddress);

            if (!$geocodeResult['success']) {
                return $this->failVerification('Address geocoding failed: ' . $geocodeResult['error']);
            }

            // Validate address components
            $validationResult = $this->validateAddressComponents($geocodeResult['data'], $addressData);

            // Perform reverse geocoding to confirm
            $reverseGeocodeResult = $this->reverseGeocode($geocodeResult['data']['geometry']['location']);

            // Calculate confidence score
            $confidenceScore = $this->calculateAddressConfidence(
                $validationResult,
                $geocodeResult['data'],
                $reverseGeocodeResult
            );

            $result = [
                'verified' => $confidenceScore >= 0.8, // 80% confidence threshold
                'confidence_score' => $confidenceScore,
                'formatted_address' => $geocodeResult['data']['formatted_address'] ?? null,
                'location' => $geocodeResult['data']['geometry']['location'] ?? null,
                'address_components' => $geocodeResult['data']['address_components'] ?? [],
                'validation_details' => $validationResult,
                'verification_provider' => 'google_maps',
                'verified_at' => now()
            ];

            // Cache result for 24 hours
            Cache::put($cacheKey, $result, 86400);

            // Update user record
            $user->update([
                'address_verified' => $result['verified'],
                'address_verified_at' => $result['verified'] ? now() : null,
                'address_verification_data' => json_encode($result),
                'address_verification_provider' => 'google_maps'
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Address verification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $this->failVerification('Address verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Geocode address using Google Maps API
     */
    private function geocodeAddress(string $address): array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/geocode/json', [
                'address' => $address,
                'key' => $this->apiKey
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'API request failed with status ' . $response->status()
                ];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['results'])) {
                return [
                    'success' => false,
                    'error' => 'Geocoding failed: ' . ($data['status'] ?? 'Unknown error')
                ];
            }

            return [
                'success' => true,
                'data' => $data['results'][0] // Use first result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Geocoding request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reverse geocode coordinates
     */
    private function reverseGeocode(array $location): ?array
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl . '/geocode/json', [
                'latlng' => $location['lat'] . ',' . $location['lng'],
                'key' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'OK' && !empty($data['results'])) {
                    return $data['results'][0];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Reverse geocoding failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Validate address components against provided data
     */
    private function validateAddressComponents(array $geocodeResult, array $providedAddress): array
    {
        $components = $geocodeResult['address_components'] ?? [];
        $validation = [
            'street_number' => false,
            'street_name' => false,
            'city' => false,
            'state' => false,
            'postal_code' => false,
            'country' => false
        ];

        foreach ($components as $component) {
            $types = $component['types'];
            $longName = $component['long_name'];

            if (in_array('street_number', $types)) {
                $validation['street_number'] = $this->matchesComponent($longName, $providedAddress['street_number'] ?? '');
            }

            if (in_array('route', $types)) {
                $validation['street_name'] = $this->matchesComponent($longName, $providedAddress['street_name'] ?? '');
            }

            if (in_array('locality', $types)) {
                $validation['city'] = $this->matchesComponent($longName, $providedAddress['city'] ?? '');
            }

            if (in_array('administrative_area_level_1', $types)) {
                $validation['state'] = $this->matchesComponent($longName, $providedAddress['state'] ?? '');
            }

            if (in_array('postal_code', $types)) {
                $validation['postal_code'] = $this->matchesComponent($longName, $providedAddress['postal_code'] ?? '');
            }

            if (in_array('country', $types)) {
                $validation['country'] = $this->matchesComponent($longName, $providedAddress['country'] ?? '');
            }
        }

        return $validation;
    }

    /**
     * Check if component matches provided value
     */
    private function matchesComponent(string $apiValue, string $providedValue): bool
    {
        if (empty($providedValue)) {
            return false;
        }

        // Normalize strings for comparison
        $apiNormalized = strtolower(trim($apiValue));
        $providedNormalized = strtolower(trim($providedValue));

        // Exact match
        if ($apiNormalized === $providedNormalized) {
            return true;
        }

        // Partial match (contains)
        if (str_contains($apiNormalized, $providedNormalized) ||
            str_contains($providedNormalized, $apiNormalized)) {
            return true;
        }

        // Fuzzy matching for common abbreviations
        return $this->fuzzyMatch($apiNormalized, $providedNormalized);
    }

    /**
     * Fuzzy matching for address components
     */
    private function fuzzyMatch(string $str1, string $str2): bool
    {
        // Simple fuzzy match - check if strings are similar
        similar_text($str1, $str2, $percent);
        return $percent > 80;
    }

    /**
     * Calculate address confidence score
     */
    private function calculateAddressConfidence(array $validation, array $geocodeData, ?array $reverseGeocode): float
    {
        $score = 0.0;
        $totalWeight = 0.0;

        // Component validation weights
        $weights = [
            'street_number' => 0.15,
            'street_name' => 0.25,
            'city' => 0.2,
            'state' => 0.15,
            'postal_code' => 0.15,
            'country' => 0.1
        ];

        foreach ($validation as $component => $matches) {
            $weight = $weights[$component] ?? 0;
            $totalWeight += $weight;

            if ($matches) {
                $score += $weight;
            }
        }

        // Bonus for exact location match
        if (isset($geocodeData['geometry']['location_type']) &&
            $geocodeData['geometry']['location_type'] === 'ROOFTOP') {
            $score += 0.1;
        }

        // Bonus for reverse geocoding consistency
        if ($reverseGeocode && isset($reverseGeocode['formatted_address'])) {
            $originalAddress = $geocodeData['formatted_address'] ?? '';
            $reverseAddress = $reverseGeocode['formatted_address'];

            if ($originalAddress === $reverseAddress) {
                $score += 0.05;
            }
        }

        return $totalWeight > 0 ? min($score / $totalWeight, 1.0) : 0.0;
    }

    /**
     * Format address data for API request
     */
    private function formatAddressForAPI(array $addressData): string
    {
        $parts = [];

        if (!empty($addressData['street_number'])) {
            $parts[] = $addressData['street_number'];
        }

        if (!empty($addressData['street_name'])) {
            $parts[] = $addressData['street_name'];
        }

        if (!empty($addressData['city'])) {
            $parts[] = $addressData['city'];
        }

        if (!empty($addressData['state'])) {
            $parts[] = $addressData['state'];
        }

        if (!empty($addressData['postal_code'])) {
            $parts[] = $addressData['postal_code'];
        }

        if (!empty($addressData['country'])) {
            $parts[] = $addressData['country'];
        }

        return implode(', ', $parts);
    }

    /**
     * Return failed verification result
     */
    private function failVerification(string $reason): array
    {
        return [
            'verified' => false,
            'error' => $reason,
            'confidence_score' => 0.0
        ];
    }

    /**
     * Get address suggestions for autocomplete
     */
    public function getAddressSuggestions(string $input, ?string $country = null): array
    {
        // Log to validate parameter type assumption
        Log::info('getAddressSuggestions called', [
            'country_type' => gettype($country),
            'country_value' => $country ?? 'null',
            'input_length' => strlen($input)
        ]);

        try {
            $params = [
                'input' => $input,
                'key' => $this->apiKey,
                'types' => 'address'
            ];

            if ($country) {
                $params['components'] = 'country:' . $country;
            }

            $response = Http::timeout(5)->get($this->baseUrl . '/place/autocomplete/json', $params);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || empty($data['predictions'])) {
                return [];
            }

            return array_map(function ($prediction) {
                return [
                    'description' => $prediction['description'],
                    'place_id' => $prediction['place_id'],
                    'types' => $prediction['types']
                ];
            }, $data['predictions']);

        } catch (\Exception $e) {
            Log::warning('Address suggestions failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get detailed place information
     */
    public function getPlaceDetails(string $placeId): ?array
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/place/details/json', [
                'place_id' => $placeId,
                'key' => $this->apiKey,
                'fields' => 'address_components,formatted_address,geometry'
            ]);

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            if ($data['status'] !== 'OK' || !isset($data['result'])) {
                return null;
            }

            return $data['result'];

        } catch (\Exception $e) {
            Log::warning('Place details request failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}