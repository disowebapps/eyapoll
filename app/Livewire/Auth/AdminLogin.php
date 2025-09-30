<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

use App\Enums\Auth\UserStatus;

class AdminLogin extends Component
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

        if (Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $admin = Auth::guard('admin')->user();

            // Check if admin status is approved
            if ($admin->status->value !== 'approved') {
                Auth::guard('admin')->logout();
                $this->addError('email', 'Account not approved. Contact administrator.');
                return;
            }

            session()->regenerate();
            session(['admin_authenticated' => true]);
            
            $this->js('window.location.href = "' . route('admin.dashboard') . '"');
            return;
        }
        
        $this->addError('email', 'Invalid credentials.');
    }

    public function render()
    {
        return view('livewire.auth.admin-login');
    }
}