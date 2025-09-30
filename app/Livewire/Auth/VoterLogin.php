<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Enums\Auth\UserStatus;
use App\Services\Auth\AuthService;

class VoterLogin extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    public $recaptcha_token = '';

    protected $rules = [
        'email' => 'required|email|max:255',
        'password' => 'required|min:6|max:255',
        // 'recaptcha_token' => 'required|string', // Temporarily disabled for development
    ];

    public function login()
    {
        Log::info('VoterLogin::login - Starting login process', [
            'email' => $this->email,
            'ip' => request()->ip()
        ]);

        $this->validate();

        $key = 'login.' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            Log::warning('VoterLogin::login - Rate limit exceeded');
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds."
            ]);
        }

        $authService = app(AuthService::class);
        $result = $authService->attemptLogin([
            'email' => $this->email,
            'password' => $this->password
        ], $this->recaptcha_token ?: null);

        Log::info('VoterLogin::login - AuthService result', [
            'success' => $result['success'],
            'requires_mfa' => $result['requires_mfa'] ?? false,
            'error' => $result['error'] ?? null
        ]);

        if (!$result['success']) {
            RateLimiter::hit($key, 300);
            $this->addError('email', $result['error'] ?? 'Login failed');
            Log::warning('VoterLogin::login - Login failed', ['error' => $result['error'] ?? 'Login failed']);
            return;
        }

        if ($result['requires_mfa']) {
            // Store user ID for MFA verification
            session(['mfa_user_id' => $result['user_id']]);
            Log::info('VoterLogin::login - MFA required, redirecting to MFA verify', ['user_id' => $result['user_id']]);
            return redirect()->route('voter.mfa.verify', ['userId' => $result['user_id']]);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        // Get the logged-in user
        $voter = Auth::user();

        Log::info('VoterLogin::login - Login successful', [
            'user_id' => $voter->id,
            'status' => $voter->status->value
        ]);

        // Auto-redirect approved users to dashboard
        if ($voter && $voter->status === UserStatus::APPROVED) {
            session()->flash('success', 'Welcome! Your account has been verified. You can now participate in elections.');
            Log::info('VoterLogin::login - Redirecting approved user to dashboard');
            return redirect()->route('voter.dashboard');
        }

        Log::info('VoterLogin::login - Redirecting to intended route');
        return redirect()->intended(route('voter.dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.voter-login');
    }
}