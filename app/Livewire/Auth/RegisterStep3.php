<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterStep3 extends Component
{
    public $email_verification_code = '';
    public $phone_verification_code = '';

    protected $rules = [
        'email_verification_code' => 'required|string|size:6',
        'phone_verification_code' => 'required|string|size:6',
    ];
    
    protected $messages = [
        'agree_terms.required' => 'You must agree to the terms and conditions.',
        'agree_terms.accepted' => 'You must agree to the terms and conditions.',
    ];

    public function mount()
    {
        // Check if previous steps are completed
        if (!\App\Services\RegistrationSessionService::getStepData(1) || 
            !\App\Services\RegistrationSessionService::getStepData(2)) {
            return redirect()->route('auth.register');
        }
        
        // Load existing data if available
        $existingData = \App\Services\RegistrationSessionService::getStepData(3);
        if ($existingData) {
            $this->email_verification_code = $existingData['email_verification_code'] ?? '';
            $this->phone_verification_code = $existingData['phone_verification_code'] ?? '';
        }

        $this->generateOtpCodes();
    }

    private function generateOtpCodes()
    {
        $step2Data = \App\Services\RegistrationSessionService::getStepData(2);
        if (!$step2Data || !isset($step2Data['email']) || !isset($step2Data['phone_number'])) {
            return;
        }
        
        $email = $step2Data['email'];
        $phone = $step2Data['phone_number'];

        // Generate random codes for both environments
        $emailOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $phoneOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        Cache::put("email_otp_{$email}", $emailOtp, now()->addMinutes(10));
        Cache::put("phone_otp_{$phone}", $phoneOtp, now()->addMinutes(10));
        
        if (app()->environment('local', 'development')) {
            // Development: Log codes for testing
            Log::info('OTP Codes Generated', [
                'email' => $email,
                'email_otp' => $emailOtp,
                'phone' => $phone,
                'phone_otp' => $phoneOtp
            ]);

            // Store in session for the view to access
            session(['dev_codes' => [
                'email_otp' => $emailOtp,
                'phone_otp' => $phoneOtp
            ]]);
        } else {
            // Production: Send email and SMS
            // Mail::to($email)->send(new OtpEmail($emailOtp));
            // SMS::send($phone, "Your verification code: {$phoneOtp}");
        }
    }

    public function nextStep()
    {
        $this->validate();
        
        if (!$this->validateOtpCodes()) {
            return;
        }
        
        // Store step 3 data
        \App\Services\RegistrationSessionService::saveStep(3, [
            'email_verification_code' => $this->email_verification_code,
            'phone_verification_code' => $this->phone_verification_code,
        ]);

        return redirect()->route('voter.register.step4');
    }

    private function validateOtpCodes()
    {
        $step2Data = \App\Services\RegistrationSessionService::getStepData(2);
        if (!$step2Data || !isset($step2Data['email']) || !isset($step2Data['phone_number'])) {
            $this->addError('email_verification_code', 'Session expired. Please restart registration.');
            return false;
        }
        
        $email = $step2Data['email'];
        $phone = $step2Data['phone_number'];

        $emailOtp = Cache::get("email_otp_{$email}");
        $phoneOtp = Cache::get("phone_otp_{$phone}");

        if ($this->email_verification_code !== $emailOtp) {
            $this->addError('email_verification_code', 'Invalid or expired email verification code.');
            return false;
        }

        if ($this->phone_verification_code !== $phoneOtp) {
            $this->addError('phone_verification_code', 'Invalid or expired phone verification code.');
            return false;
        }

        // Store verification in database for reliability
        DB::table('pending_verifications')->updateOrInsert(
            ['email' => $email, 'phone_number' => $phone],
            [
                'email_otp' => $emailOtp,
                'phone_otp' => $phoneOtp,
                'email_verified' => true,
                'phone_verified' => true,
                'expires_at' => now()->addHour(),
                'updated_at' => now()
            ]
        );

        return true;
    }

    private function clearOtpCodes($email, $phone)
    {
        Cache::forget("email_otp_{$email}");
        Cache::forget("phone_otp_{$phone}");
    }

    public function resendEmailCode()
    {
        $step2Data = \App\Services\RegistrationSessionService::getStepData(2);
        if (!$step2Data || !isset($step2Data['email'])) {
            return;
        }
        
        $email = $step2Data['email'];
        
        $emailOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("email_otp_{$email}", $emailOtp, now()->addMinutes(10));
        
        if (app()->environment('local', 'development')) {
            Log::info('Email OTP Resent', ['email' => $email, 'otp' => $emailOtp]);
            $currentDevCodes = session('dev_codes', []);
            $currentDevCodes['email_otp'] = $emailOtp;
            session(['dev_codes' => $currentDevCodes]);
        } else {
            // TODO: Send email
        }
        
        session()->flash('message', 'Email verification code resent.');
    }

    public function resendPhoneCode()
    {
        $step2Data = \App\Services\RegistrationSessionService::getStepData(2);
        if (!$step2Data || !isset($step2Data['phone_number'])) {
            return;
        }
        
        $phone = $step2Data['phone_number'];
        
        $phoneOtp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("phone_otp_{$phone}", $phoneOtp, now()->addMinutes(10));
        
        if (app()->environment('local', 'development')) {
            Log::info('Phone OTP Resent', ['phone' => $phone, 'otp' => $phoneOtp]);
            $currentDevCodes = session('dev_codes', []);
            $currentDevCodes['phone_otp'] = $phoneOtp;
            session(['dev_codes' => $currentDevCodes]);
        } else {
            // TODO: Send SMS
        }
        
        session()->flash('message', 'Phone verification code resent.');
    }

    public function previousStep()
    {
        return redirect()->route('voter.register.step2');
    }

    private function getVerificationTimestamp($email, $phone, $type)
    {
        $record = DB::table('pending_verifications')
            ->where('email', $email)
            ->where('phone_number', $phone)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$record) return null;
        
        $isVerified = $type === 'email' ? $record->email_verified : $record->phone_verified;
        return $isVerified ? now() : null;
    }

    public function render()
    {
        return view('livewire.auth.register-step3');
    }
}