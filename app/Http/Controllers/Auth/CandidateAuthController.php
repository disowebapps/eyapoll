<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CandidateAuthController extends Controller
{
    public function showLoginForm()
    {
        try {
            Log::info('CandidateAuth: Showing login form - ' . now());
            return view('auth.candidate-login');
        } catch (\Exception $e) {
            Log::error('CandidateAuth: Error showing login form: ' . $e->getMessage());
            return response('<h1>Error: ' . $e->getMessage() . '</h1>', 500);
        }
    }

    public function login(Request $request)
    {
        Log::info('CandidateAuth: Login attempt', ['email' => $request->email]);
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('candidate')->attempt($credentials, $request->boolean('remember'))) {
            Log::info('CandidateAuth: Login successful');
            $request->session()->regenerate();

            $candidate = Auth::guard('candidate')->user();
            Log::info('CandidateAuth: Candidate status', ['status' => $candidate->status->value]);
            
            if ($candidate->status->value !== 'approved') {
                Log::warning('CandidateAuth: Account not approved', ['email' => $request->email]);
                Auth::guard('candidate')->logout();
                throw ValidationException::withMessages([
                    'email' => 'Account not approved. Please contact administrator.',
                ]);
            }

            Log::info('CandidateAuth: Redirecting to dashboard');
            return redirect()->intended(route('candidate.dashboard'));
        }

        Log::warning('CandidateAuth: Invalid credentials', ['email' => $request->email]);
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('candidate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('candidate.login');
    }
}