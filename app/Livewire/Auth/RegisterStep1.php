<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterStep1 extends Component
{
    public $first_name = '';
    public $last_name = '';
    public $role = 'voter';

    protected $rules = [
        'first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        'last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
    ];

    protected $messages = [
        'first_name.regex' => 'First name can only contain letters and spaces.',
        'last_name.regex' => 'Last name can only contain letters and spaces.',
        'phone_number.regex' => 'Please enter a valid phone number.',
        'id_number.regex' => 'ID number must be exactly 11 digits.',
        'id_number.unique' => 'This ID number is already registered.',
        'email.unique' => 'This email address is already registered.',
    ];

    public function completeRegistration()
    {
        $this->validate();

        // Store step 1 data persistently
        \App\Services\RegistrationSessionService::saveStep(1, [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'role' => $this->role,
        ]);

        return redirect()->route('voter.register.step2');
    }

    public function mount()
    {
        // Check if voter registration is enabled
        if (!\App\Services\VoterRegistrationService::isEnabled()) {
            session()->flash('error', 'Voter registration is currently paused. Please try again later.');
            return redirect()->route('home');
        }
        
        // Load existing data if available
        $existingData = \App\Services\RegistrationSessionService::getStepData(1);
        if ($existingData) {
            $this->first_name = $existingData['first_name'] ?? '';
            $this->last_name = $existingData['last_name'] ?? '';
            $this->role = $existingData['role'] ?? 'voter';
        }
    }

    public function render()
    {
        return view('livewire.auth.register-step1');
    }
}