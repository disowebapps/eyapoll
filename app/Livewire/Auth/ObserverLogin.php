<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Enums\Auth\UserStatus;

class ObserverLogin extends Component
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
        $this->validate();

        $key = 'login.' . request()->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds."
            ]);
        }

        if (Auth::guard('observer')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $observer = Auth::guard('observer')->user();
            
            if ($observer->status !== UserStatus::APPROVED) {
                Auth::guard('observer')->logout();
                RateLimiter::hit($key, 300);
                $this->addError('email', 'Account not approved. Contact administrator.');
                return;
            }

            RateLimiter::clear($key);
            session()->regenerate();
            return redirect()->intended(route('observer.dashboard'));
        }

        RateLimiter::hit($key, 300);
        $this->addError('email', 'Invalid credentials.');
    }

    public function render()
    {
        return view('livewire.auth.observer-login');
    }
}