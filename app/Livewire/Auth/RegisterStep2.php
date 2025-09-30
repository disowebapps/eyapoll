<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterStep2 extends Component
{
    public $email = '';
    public $phone_number = '';

    protected $rules = [
        'email' => 'required|email|unique:users,email',
        'phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
    ];

    protected $messages = [
        'phone_number.regex' => 'Please enter a valid phone number.',
        'email.unique' => 'This email address is already registered.',
    ];

    public function mount()
    {
        // Check if step 1 is completed
        if (!\App\Services\RegistrationSessionService::getStepData(1)) {
            return redirect()->route('voter.register');
        }
        
        // Load existing data if available
        $existingData = \App\Services\RegistrationSessionService::getStepData(2);
        if ($existingData) {
            $this->email = $existingData['email'] ?? '';
            $this->phone_number = $existingData['phone_number'] ?? '';
        }
    }

    public function nextStep()
    {
        $this->validate();

        // Store step 2 data persistently
        \App\Services\RegistrationSessionService::saveStep(2, [
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ]);

        return redirect()->route('voter.register.step3');
    }

    public function previousStep()
    {
        return redirect()->route('voter.register');
    }

    public function render()
    {
        return view('livewire.auth.register-step2');
    }
}