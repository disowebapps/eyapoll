<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Enums\Candidate\CandidateStatus;

class CandidateLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email|max:255',
        'password' => 'required|min:6|max:255',
    ];

    public function login()
    {
        Log::info('CandidateLogin Livewire: Login attempt', ['email' => $this->email]);
        $this->validate();

        $key = 'login.' . request()->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds."
            ]);
        }

        if (Auth::guard('candidate')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            Log::info('CandidateLogin Livewire: Auth successful');
            $candidate = Auth::guard('candidate')->user();
            Log::info('CandidateLogin Livewire: Candidate status', ['status' => $candidate->status->value]);
            
            if ($candidate->status !== CandidateStatus::APPROVED) {
                Log::warning('CandidateLogin Livewire: Account not approved', ['email' => $this->email]);
                Auth::guard('candidate')->logout();
                RateLimiter::hit($key, 300);
                $this->addError('email', 'Account not approved. Contact administrator.');
                return;
            }

            Log::info('CandidateLogin Livewire: Redirecting to dashboard');
            RateLimiter::clear($key);
            session()->regenerate();
            return redirect()->intended(route('candidate.dashboard'));
        }

        Log::warning('CandidateLogin Livewire: Invalid credentials', ['email' => $this->email]);
        RateLimiter::hit($key, 300);
        $this->addError('email', 'Invalid credentials.');
    }

    public function render()
    {
        Log::info('CandidateLogin Livewire: Rendering component');
        return view('livewire.auth.candidate-login');
    }
}