<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Services\Auth\AuthService;

class VoterMfaVerify extends Component
{
    public $userId;
    public $email_code = '';
    public $phone_code = '';

    protected $rules = [
        'email_code' => 'required|string|size:6',
        'phone_code' => 'nullable|string|size:6',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?? session('mfa_user_id');

        if (!$this->userId) {
            return redirect()->route('voter.login');
        }
    }

    public function verifyMfa()
    {
        $this->validate();

        $key = 'mfa.verify.' . $this->userId;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email_code' => "Too many verification attempts. Please try again in {$seconds} seconds."
            ]);
        }

        $authService = app(AuthService::class);
        $result = $authService->verifyMfaCodes($this->userId, $this->email_code, $this->phone_code);

        if ($result['success']) {
            RateLimiter::clear($key);
            session()->forget('mfa_user_id');

            return redirect($result['redirect_url']);
        }

        RateLimiter::hit($key, 300); // 5 minutes lockout

        if (isset($result['error'])) {
            $this->addError('email_code', $result['error']);
        }
    }

    public function resendCodes()
    {
        try {
            $authService = app(AuthService::class);
            $authService->resendMfaCodes($this->userId);

            session()->flash('success', 'New verification codes have been sent to your email and phone.');
        } catch (\RuntimeException $e) {
            $this->addError('email_code', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.auth.voter-mfa-verify');
    }
}
