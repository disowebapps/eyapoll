<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ObserverAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.observer-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('observer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $observer = Auth::guard('observer')->user();
            if ($observer->status->value !== 'approved') {
                Auth::guard('observer')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Account not approved. Please contact administrator.',
                ]);
            }

            return redirect()->intended(route('observer.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('observer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('observer.login');
    }
}