<?php

namespace App\Services\Auth;

use App\Services\Cryptographic\CryptographicService;
use App\Services\RetryService;
use App\Exceptions\ExternalServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    public function __construct(
        private CryptographicService $crypto
    ) {}

    /**
     * Validate reCAPTCHA v3 token
     */
    public function validateRecaptcha(?string $token, float $minScore = 0.5): bool
    {
        // Skip validation in development if configured
        if (config('ayapoll.development.bypass_recaptcha', false)) {
            return true;
        }

        $secretKey = config('services.recaptcha.secret_key');
        
        if (!$secretKey) {
            Log::warning('reCAPTCHA secret key not configured');
            return false;
        }

        try {
            $result = RetryService::retryHttp(function () use ($secretKey, $token) {
                $response = Http::timeout(10)->asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

                return $response->json();
            }, 2);

            if (!$result['success']) {
                Log::warning('reCAPTCHA validation failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => request()->ip(),
                ]);
                return false;
            }

            // Check score for v3
            if (isset($result['score']) && $result['score'] < $minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $result['score'],
                    'min_score' => $minScore,
                    'ip' => request()->ip(),
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA validation error after retries', [
                'error' => $e->getMessage(),
                'ip' => request()->ip(),
            ]);
            throw new ExternalServiceException('reCAPTCHA service unavailable', [], 0, $e);
        }
    }

    /**
     * Validate ID number format
     */
    public function validateIdNumber(string $idNumber): bool
    {
        // Remove any spaces or special characters
        $cleaned = preg_replace('/[^0-9]/', '', $idNumber);
        
        // Check length (assuming Nigerian NIN format - 11 digits)
        if (strlen($cleaned) !== 11) {
            return false;
        }

        // Check if all digits
        if (!ctype_digit($cleaned)) {
            return false;
        }

        return true;
    }

    /**
     * Validate phone number format
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Remove any spaces, dashes, or special characters
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Nigerian phone number patterns
        $patterns = [
            '/^\+234[789][01]\d{8}$/',  // +234 format
            '/^234[789][01]\d{8}$/',   // 234 format
            '/^0[789][01]\d{8}$/',     // 0 format
            '/^[789][01]\d{8}$/',      // Without country code
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleaned)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize phone number to international format
     */
    public function normalizePhoneNumber(string $phoneNumber): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phoneNumber);
        
        // Convert to +234 format
        if (preg_match('/^0([789][01]\d{8})$/', $cleaned, $matches)) {
            return '+234' . $matches[1];
        }
        
        if (preg_match('/^234([789][01]\d{8})$/', $cleaned, $matches)) {
            return '+234' . $matches[1];
        }
        
        if (preg_match('/^([789][01]\d{8})$/', $cleaned, $matches)) {
            return '+234' . $matches[1];
        }
        
        if (preg_match('/^\+234([789][01]\d{8})$/', $cleaned, $matches)) {
            return '+234' . $matches[1];
        }

        return $cleaned; // Return as-is if no pattern matches
    }

    /**
     * Validate email format and domain
     */
    public function validateEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Check for disposable email domains (basic list)
        $disposableDomains = [
            '10minutemail.com',
            'tempmail.org',
            'guerrillamail.com',
            'mailinator.com',
            'throwaway.email',
        ];

        $domain = strtolower(substr(strrchr($email, "@"), 1));
        
        return !in_array($domain, $disposableDomains);
    }

    /**
     * Check for duplicate users by ID number
     */
    public function checkDuplicateIdNumber(string $idNumber, ?int $excludeUserId = null): bool
    {
        $salt = $this->crypto->generateSalt();
        $hashedId = $this->crypto->hashIdNumber($idNumber, $salt);
        
        $query = \App\Models\User::where('id_number_hash', $hashedId);
        
        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }
        
        return $query->exists();
    }

    /**
     * Validate document file
     */
    public function validateDocumentFile(\Illuminate\Http\UploadedFile $file): array
    {
        $errors = [];

        // Check file size (5MB max)
        if ($file->getSize() > 5 * 1024 * 1024) {
            $errors[] = 'File size must be less than 5MB';
        }

        // Check file type
        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File must be a JPEG, PNG, or PDF';
        }

        // Check file extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Invalid file extension';
        }

        // Basic virus scan (check for suspicious patterns)
        if ($this->containsSuspiciousContent($file)) {
            $errors[] = 'File contains suspicious content';
        }

        return $errors;
    }

    /**
     * Basic content scanning for uploaded files
     */
    private function containsSuspiciousContent(\Illuminate\Http\UploadedFile $file): bool
    {
        // For PDF files, check for JavaScript or suspicious content
        if ($file->getMimeType() === 'application/pdf') {
            $content = file_get_contents($file->path());
            
            $suspiciousPatterns = [
                '/\/JavaScript/i',
                '/\/JS/i',
                '/\/Action/i',
                '/<script/i',
                '/javascript:/i',
            ];

            foreach ($suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Generate secure verification code
     */
    public function generateVerificationCode(int $length = 6, string $type = 'numeric'): string
    {
        return $this->crypto->generateVerificationCode($length, $type);
    }

    /**
     * Verify code with rate limiting
     */
    public function verifyCodeWithRateLimit(string $key, string $providedCode, string $storedHash, int $maxAttempts = 5): bool
    {
        $attemptKey = "verification-attempts:{$key}";
        
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($attemptKey, $maxAttempts)) {
            return false;
        }

        if (!\Illuminate\Support\Facades\Hash::check($providedCode, $storedHash)) {
            \Illuminate\Support\Facades\RateLimiter::hit($attemptKey, 300); // 5 minutes
            return false;
        }

        \Illuminate\Support\Facades\RateLimiter::clear($attemptKey);
        return true;
    }

    /**
     * Clean expired verification data
     */
    public function cleanExpiredVerificationData(): int
    {
        $users = \App\Models\User::whereNotNull('verification_data')->get();
        $cleaned = 0;

        foreach ($users as $user) {
            try {
                $verificationData = $this->crypto->decryptData($user->verification_data);
                $updated = false;

                // Clean expired email verification
                if (isset($verificationData['email_verification'])) {
                    $expiresAt = $verificationData['email_verification']['expires_at'];
                    if (now()->isAfter($expiresAt)) {
                        unset($verificationData['email_verification']);
                        $updated = true;
                    }
                }

                // Clean expired phone verification
                if (isset($verificationData['phone_verification'])) {
                    $expiresAt = $verificationData['phone_verification']['expires_at'];
                    if (now()->isAfter($expiresAt)) {
                        unset($verificationData['phone_verification']);
                        $updated = true;
                    }
                }

                // Clean expired MFA codes
                foreach (['email_mfa', 'phone_mfa'] as $mfaType) {
                    if (isset($verificationData[$mfaType])) {
                        $expiresAt = $verificationData[$mfaType]['expires_at'];
                        if (now()->isAfter($expiresAt)) {
                            unset($verificationData[$mfaType]);
                            $updated = true;
                        }
                    }
                }

                if ($updated) {
                    $user->update([
                        'verification_data' => empty($verificationData) ? null : $this->crypto->encryptData($verificationData)
                    ]);
                    $cleaned++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to clean verification data for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $cleaned;
    }
}