<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VoterAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.voter-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $voter = Auth::guard('web')->user();
            if ($voter->status->value !== 'approved') {
                Auth::guard('web')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Account not approved. Please contact administrator.',
                ]);
            }

            return redirect()->intended(route('voter.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('voter.login');
    }
}