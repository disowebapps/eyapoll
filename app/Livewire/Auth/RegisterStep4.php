<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisterStep4 extends Component
{
    public $password = '';
    public $password_confirmation = '';
    public $agree_terms = false;

    protected $rules = [
        'password' => 'required|string|min:8|confirmed',
        'agree_terms' => 'required|accepted',
    ];
    
    protected $messages = [
        'agree_terms.required' => 'You must agree to the terms and conditions.',
        'agree_terms.accepted' => 'You must agree to the terms and conditions.',
    ];

    public function mount()
    {
        // Check if previous steps are completed
        if (!\App\Services\RegistrationSessionService::getStepData(1) || 
            !\App\Services\RegistrationSessionService::getStepData(2) ||
            !\App\Services\RegistrationSessionService::getStepData(3)) {
            return redirect()->route('voter.register');
        }
        
        // Load existing data if available
        $existingData = \App\Services\RegistrationSessionService::getStepData(4);
        if ($existingData) {
            $this->agree_terms = $existingData['agree_terms'] ?? false;
        }
    }

    public function completeRegistration()
    {
        $this->validate();
        
        try {
            $step1Data = \App\Services\RegistrationSessionService::getStepData(1);
            $step2Data = \App\Services\RegistrationSessionService::getStepData(2);
            
            // Create user with PENDING status for admin approval
            $userData = [
                'uuid' => Str::uuid(),
                'first_name' => $step1Data['first_name'],
                'last_name' => $step1Data['last_name'],
                'email' => $step2Data['email'],
                'phone_number' => $step2Data['phone_number'],
                'password' => bcrypt($this->password),
                'id_number_hash' => hash('sha256', 'pending_kyc_' . $step2Data['email'] . time()),
                'id_salt' => Str::random(32),
                'role' => UserRole::VOTER,
                'status' => UserStatus::PENDING,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'verification_data' => ['status' => 'verified'],
            ];
            
            $user = User::create($userData);
            
            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->first_name . ' ' . $user->last_name
            ]);

            // Clear session data
            \App\Services\RegistrationSessionService::clearSession();
            
            return redirect()->route('voter.registration-complete');
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Registration failed. Please try again.');
            return;
        }
    }

    public function previousStep()
    {
        return redirect()->route('voter.register.step3');
    }

    public function render()
    {
        return view('livewire.auth.register-step4');
    }
}