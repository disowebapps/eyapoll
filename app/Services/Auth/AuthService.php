<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Auth\IdDocument;
use App\Events\Auth\UserRegistered;
use App\Events\Auth\UserApproved;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Notification\NotificationService;
use App\Services\Audit\AuditLogService;
use App\Services\Security\FilePathValidator;
use App\Enums\Auth\UserStatus;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\DocumentType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;


class AuthService
{
    public function __construct(
        private CryptographicService $crypto,
        private NotificationService $notifications,
        private VerificationService $verification,
        private AuditLogService $auditLog,
        private FilePathValidator $pathValidator
    ) {}

    /**
     * Step 1: Initial Registration
     */
    public function registerUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Generate unique identifiers
            $uuid = Str::uuid();
            $idSalt = $this->crypto->generateSalt();
            $idNumberHash = $this->crypto->hashIdNumber($data['id_number'], $idSalt);

            // Check for duplicate ID number
            if (User::where('id_number_hash', $idNumberHash)->exists()) {
                throw new \InvalidArgumentException('This ID number is already registered.');
            }

            $user = User::create([
                'uuid' => $uuid,
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'id_number_hash' => $idNumberHash,
                'id_salt' => $idSalt,
                'status' => UserStatus::PENDING,
                'role' => UserRole::from($data['role'] ?? 'voter'),
                'verification_data' => $this->crypto->encryptData([
                    'registration_ip' => request()->ip(),
                    'registration_user_agent' => request()->userAgent(),
                    'registration_timestamp' => now()->toISOString(),
                ]),
            ]);

            // Log the registration
            $this->auditLog->log('user_registered', $user, User::class, $user->id);

            // Fire event for notifications
            event(new UserRegistered($user));

            return $user;
        });
    }

    /**
     * Step 2: Email Verification
     */
    public function sendEmailVerification(User $user): void
    {
        if ($user->email_verified_at) {
            throw new \InvalidArgumentException('Email is already verified.');
        }

        // Rate limiting
        $key = 'email-verification:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw new \RuntimeException('Too many verification attempts. Please try again later.');
        }

        $code = $this->crypto->generateVerificationCode(6, 'numeric');
        $token = $this->generateVerificationToken($user, 'email', $code);
        
        // Store verification data
        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        $verificationData['email_verification'] = [
            'code' => Hash::make($code),
            'token' => $token,
            'expires_at' => now()->addHours(24)->toISOString(),
            'attempts' => 0,
        ];
        
        $user->update([
            'verification_data' => $this->crypto->encryptData($verificationData)
        ]);

        // Send notification
        $this->notifications->send($user, 'email_verification', [
            'code' => $code,
            'expires_at' => now()->addHours(24),
        ], 'email');

        RateLimiter::hit($key, 3600); // 1 hour cooldown
        
        $this->auditLog->log('email_verification_sent', $user, User::class, $user->id);
    }

    /**
     * Verify email code
     */
    public function verifyEmailCode(User $user, string $code): bool
    {
        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        $emailVerification = $verificationData['email_verification'] ?? null;

        if (!$emailVerification) {
            return false;
        }

        // Check expiry
        if (now()->isAfter($emailVerification['expires_at'])) {
            return false;
        }

        // Check attempts
        if (($emailVerification['attempts'] ?? 0) >= 5) {
            return false;
        }

        // Verify code
        if (!Hash::check($code, $emailVerification['code'])) {
            // Increment attempts
            $emailVerification['attempts'] = ($emailVerification['attempts'] ?? 0) + 1;
            $verificationData['email_verification'] = $emailVerification;
            
            $user->update([
                'verification_data' => $this->crypto->encryptData($verificationData)
            ]);
            
            return false;
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
        ]);

        $this->auditLog->log('email_verified', $user, User::class, $user->id);

        return true;
    }

    /**
     * Step 3: Phone Verification (Optional)
     */
    public function sendPhoneVerification(User $user): void
    {
        if (!$user->phone_number) {
            throw new \InvalidArgumentException('No phone number provided.');
        }

        if ($user->phone_verified_at) {
            throw new \InvalidArgumentException('Phone is already verified.');
        }

        // Rate limiting
        $key = 'phone-verification:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw new \RuntimeException('Too many verification attempts. Please try again later.');
        }

        $code = $this->crypto->generateVerificationCode(6, 'numeric');
        
        // Store verification data
        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        $verificationData['phone_verification'] = [
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->toISOString(),
            'attempts' => 0,
        ];
        
        $user->update([
            'verification_data' => $this->crypto->encryptData($verificationData)
        ]);

        // Send SMS notification
        $this->notifications->send($user, 'phone_verification', [
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ], 'sms');

        RateLimiter::hit($key, 600); // 10 minutes cooldown
        
        $this->auditLog->log('phone_verification_sent', $user, User::class, $user->id);
    }

    /**
     * Verify phone code
     */
    public function verifyPhoneCode(User $user, string $code): bool
    {
        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        $phoneVerification = $verificationData['phone_verification'] ?? null;

        if (!$phoneVerification) {
            return false;
        }

        // Check expiry
        if (now()->isAfter($phoneVerification['expires_at'])) {
            return false;
        }

        // Check attempts
        if (($phoneVerification['attempts'] ?? 0) >= 5) {
            return false;
        }

        // Verify code
        if (!Hash::check($code, $phoneVerification['code'])) {
            // Increment attempts
            $phoneVerification['attempts'] = ($phoneVerification['attempts'] ?? 0) + 1;
            $verificationData['phone_verification'] = $phoneVerification;
            
            $user->update([
                'verification_data' => $this->crypto->encryptData($verificationData)
            ]);
            
            return false;
        }

        // Mark phone as verified
        $user->update([
            'phone_verified_at' => now(),
        ]);

        $this->auditLog->log('phone_verified', $user, User::class, $user->id);

        return true;
    }

    /**
     * Step 4: ID Document Upload
     */
    public function uploadIdDocument(User $user, array $documentData): IdDocument
    {
        if (!$user->email_verified_at) {
            throw new \InvalidArgumentException('Email must be verified before uploading ID document.');
        }

        // Allow upload for PENDING and REJECTED users
        if (!in_array($user->status, [UserStatus::PENDING, UserStatus::REJECTED])) {
            throw new \InvalidArgumentException('Document upload not allowed for current user status.');
        }

        // Check resubmission limit
        if ($user->hasExceededResubmissionLimit()) {
            throw new \InvalidArgumentException('Maximum resubmission attempts exceeded. Please contact support.');
        }

        return DB::transaction(function () use ($user, $documentData) {
            $file = $documentData['file'];
            $documentType = DocumentType::from($documentData['type']);
            
            // Validate file
            $this->validateDocumentFile($file, $documentType);
            
            // Store file securely
            $filePath = $this->storeDocument($file, $user, $documentType);
            $fileHash = hash_file('sha256', $file->path());

            $document = IdDocument::create([
                'user_id' => $user->id,
                'document_type' => $documentType,
                'file_path' => $filePath,
                'file_hash' => $fileHash,
                'status' => 'pending',
            ]);

            $this->auditLog->log('id_document_uploaded', $user, IdDocument::class, $document->id);

            return $document;
        });
    }

    /**
     * Admin Approval Process
     */
    public function approveUser(User $user, User $admin, ?string $reason = null): void
    {
        if (!$admin->canApproveUsers()) {
            throw new \InvalidArgumentException('Insufficient permissions to approve users.');
        }

        if ($user->status !== UserStatus::REVIEW) {
            throw new \InvalidArgumentException('User is not in review status.');
        }

        DB::transaction(function () use ($user, $admin, $reason) {
            $oldStatus = $user->status;
            
            $user->update([
                'status' => UserStatus::APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // Approve associated ID document
            $user->idDocuments()->pending()->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $this->auditLog->log(
                'user_approved',
                $admin,
                User::class,
                $user->id,
                ['status' => $oldStatus->value],
                ['status' => UserStatus::APPROVED->value, 'reason' => $reason]
            );

            event(new UserApproved($user, $admin, $reason));
        });
    }

    /**
     * Reject user
     */
    public function rejectUser(User $user, User $admin, string $reason): void
    {
        if (!$admin->canApproveUsers()) {
            throw new \InvalidArgumentException('Insufficient permissions to reject users.');
        }

        if ($user->status !== UserStatus::REVIEW) {
            throw new \InvalidArgumentException('User is not in review status.');
        }

        DB::transaction(function () use ($user, $admin, $reason) {
            $oldStatus = $user->status;
            
            $user->update([
                'status' => UserStatus::REJECTED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]);

            // Reject associated ID document
            $user->idDocuments()->pending()->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            $this->auditLog->log(
                'user_rejected',
                $admin,
                User::class,
                $user->id,
                ['status' => $oldStatus->value],
                ['status' => UserStatus::REJECTED->value, 'reason' => $reason]
            );
        });
    }

    /**
     * Multi-Factor Authentication Login
     */
    public function attemptLogin(array $credentials, ?string $recaptchaToken): array
    {
        Log::info('AuthService::attemptLogin - Starting login attempt', [
            'email' => $credentials['email'] ?? 'unknown',
            'ip' => request()->ip()
        ]);

        // Validate reCAPTCHA
        if (!$this->verification->validateRecaptcha($recaptchaToken)) {
            Log::warning('AuthService::attemptLogin - reCAPTCHA validation failed');
            $this->auditLog->log('login_failed', null, null, null, null, ['reason' => 'reCAPTCHA validation failed']);
            return ['success' => false, 'error' => 'reCAPTCHA validation failed'];
        }

        Log::info('AuthService::attemptLogin - reCAPTCHA passed');

        // Rate limiting
        $key = 'login-attempts:' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Log::warning('AuthService::attemptLogin - Rate limit exceeded');
            $this->auditLog->log('login_failed', null, null, null, null, ['reason' => 'Rate limit exceeded']);
            return ['success' => false, 'error' => 'Too many login attempts. Please try again later.'];
        }

        Log::info('AuthService::attemptLogin - Rate limit check passed');

        // Attempt authentication
        if (!Auth::attempt($credentials)) {
            Log::warning('AuthService::attemptLogin - Invalid credentials for email: ' . ($credentials['email'] ?? 'unknown'));
            RateLimiter::hit($key, 300); // 5 minutes lockout
            $this->auditLog->log('login_failed', null, null, null, null, ['reason' => 'Invalid credentials']);
            return ['success' => false, 'error' => 'Invalid credentials'];
        }

        $user = Auth::user();
        Log::info('AuthService::attemptLogin - Auth::attempt succeeded', [
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => $user->status->value
        ]);

        // Check user status - allow PENDING, REVIEW, APPROVED, and ACCREDITED users
        if (!in_array($user->status, [UserStatus::PENDING, UserStatus::REVIEW, UserStatus::APPROVED, UserStatus::ACCREDITED])) {
            Log::warning('AuthService::attemptLogin - User status not allowed', [
                'user_id' => $user->id,
                'status' => $user->status->value
            ]);
            Auth::logout();
            $this->auditLog->log('login_failed', $user, User::class, $user->id, null, ['reason' => 'Account suspended or rejected']);
            return [
                'success' => false,
                'error' => "Account status: {$user->status->label()}",
                'status' => $user->status->value
            ];
        }

        Log::info('AuthService::attemptLogin - User status check passed');

        // Send MFA codes if enabled
        if (config('ayapoll.security.mfa_enabled', true)) {
            Log::info('AuthService::attemptLogin - MFA enabled, sending codes');
            $this->sendMfaCodes($user);

            // Temporarily logout until MFA is completed
            Auth::logout();

            return [
                'success' => true,
                'requires_mfa' => true,
                'user_id' => $user->id
            ];
        }

        Log::info('AuthService::attemptLogin - MFA disabled, login successful');

        RateLimiter::clear($key);
        $this->auditLog->log('login_success', $user, User::class, $user->id);

        return [
            'success' => true,
            'requires_mfa' => false,
            'redirect_url' => $user->role->dashboardRoute()
        ];
    }

    /**
     * Verify MFA codes
     */
    public function verifyMfaCodes(int $userId, string $emailCode, ?string $phoneCode = null): array
    {
        $user = User::findOrFail($userId);
        
        if (!in_array($user->status, [UserStatus::APPROVED, UserStatus::ACCREDITED])) {
            return ['success' => false, 'error' => 'Account not approved'];
        }

        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        
        // Verify email MFA code
        $emailMfa = $verificationData['email_mfa'] ?? null;
        if (!$emailMfa || !Hash::check($emailCode, $emailMfa['code'])) {
            $this->auditLog->log('mfa_failed', $user, User::class, $user->id, null, ['reason' => 'Invalid email code']);
            return ['success' => false, 'error' => 'Invalid email verification code'];
        }

        // Check email code expiry
        if (now()->isAfter($emailMfa['expires_at'])) {
            return ['success' => false, 'error' => 'Email verification code has expired'];
        }

        // Verify phone MFA code if provided and required
        if ($user->phone_verified_at && $phoneCode) {
            $phoneMfa = $verificationData['phone_mfa'] ?? null;
            if (!$phoneMfa || !Hash::check($phoneCode, $phoneMfa['code'])) {
                $this->auditLog->log('mfa_failed', $user, User::class, $user->id, null, ['reason' => 'Invalid phone code']);
                return ['success' => false, 'error' => 'Invalid phone verification code'];
            }

            // Check phone code expiry
            if (now()->isAfter($phoneMfa['expires_at'])) {
                return ['success' => false, 'error' => 'Phone verification code has expired'];
            }
        }

        // Login the user
        Auth::login($user, true);
        
        $this->auditLog->log('login_success', $user, User::class, $user->id);

        return [
            'success' => true,
            'redirect_url' => $user->role->dashboardRoute()
        ];
    }

    /**
     * Send MFA codes
     */
    private function sendMfaCodes(User $user): void
    {
        // Email MFA
        $emailCode = $this->crypto->generateVerificationCode(6, 'numeric');
        $this->storeMfaCode($user, 'email_mfa', $emailCode);

        $this->notifications->send($user, 'login_verification', [
            'code' => $emailCode,
            'expires_at' => now()->addMinutes(10),
        ], 'email');

        // Phone MFA (if enabled and available)
        if ($user->phone_verified_at && config('ayapoll.notification_channels.sms', false)) {
            $phoneCode = $this->crypto->generateVerificationCode(6, 'numeric');
            $this->storeMfaCode($user, 'phone_mfa', $phoneCode);

            $this->notifications->send($user, 'login_verification', [
                'code' => $phoneCode,
                'expires_at' => now()->addMinutes(10),
            ], 'sms');
        }
    }

    /**
     * Resend MFA codes for a user
     */
    public function resendMfaCodes(int $userId): void
    {
        $user = User::findOrFail($userId);

        // Rate limiting for resend
        $key = 'mfa.resend:' . $userId;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw new \RuntimeException('Too many resend requests. Please wait before trying again.');
        }

        $this->sendMfaCodes($user);
        RateLimiter::hit($key, 300); // 5 minutes cooldown
    }

    /**
     * Store MFA code securely
     */
    private function storeMfaCode(User $user, string $type, string $code): void
    {
        $verificationData = $this->crypto->decryptData($user->verification_data ?? '{}');
        $verificationData[$type] = [
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(10)->toISOString(),
            'attempts' => 0,
        ];
        
        $user->update([
            'verification_data' => $this->crypto->encryptData($verificationData)
        ]);
    }

    /**
     * Generate verification token
     */
    private function generateVerificationToken(User $user, string $type, ?string $code = null): string
    {
        $data = [
            'user_id' => $user->id,
            'type' => $type,
            'timestamp' => now()->timestamp,
            'random' => Str::random(16),
        ];

        if ($code) {
            $data['code_hash'] = hash('sha256', $code);
        }

        return hash('sha256', json_encode($data));
    }

    /**
     * Store document securely
     */
    private function storeDocument(UploadedFile $file, User $user, DocumentType $documentType): string
    {
        $secureFileService = app(\App\Services\Security\SecureFileService::class);
        $filename = $secureFileService->store($file);

        // Encrypt the filename for storage in DB
        return encrypt($filename);
    }

    /**
     * Validate document file
     */
    private function validateDocumentFile(UploadedFile $file, DocumentType $documentType): void
    {
        $rules = $documentType->validationRules();
        
        $validator = Validator::make(['file' => $file], ['file' => $rules]);
        
        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid document file: ' . $validator->errors()->first());
        }
    }

    /**
     * Get pending users for approval
     */
    public function getPendingUsers(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::where('status', UserStatus::REVIEW)
            ->with(['idDocuments'])
            ->whereHas('idDocuments', function ($query) {
                $query->where('status', 'pending');
            })
            ->orderBy('created_at')
            ->paginate(20);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $user = Auth::user();
        
        if ($user) {
            $this->auditLog->log('logout', $user, User::class, $user->id);
        }
        
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}