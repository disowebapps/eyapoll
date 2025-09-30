<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Redirect to unified voter login
Route::get('/login', function () {
    return redirect()->route('voter.login');
})->name('login');

// Redirect POST login to voter login
Route::post('/login', function () {
    return redirect()->route('voter.login');
});

Route::middleware(['throttle.login'])->group(function () {
    Route::post('/voter/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Check if this is admin login
    if ($request->has('admin_login')) {
        Log::info('Admin Login Attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $admin = \App\Models\Admin::where('email', $credentials['email'])->first();
        
        Log::info('Admin Login - User lookup', [
            'admin_found' => $admin ? 'yes' : 'no',
            'admin_id' => $admin->id ?? 'null',
            'admin_status' => $admin->status ?? 'null'
        ]);
        
        if ($admin && \Illuminate\Support\Facades\Hash::check($credentials['password'], $admin->password)) {
            $request->session()->put('admin_user', $admin);
            $request->session()->regenerate();
            
            Log::info('Admin Login Success', [
                'admin_id' => $admin->id,
                'admin_email' => $admin->email,
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('admin.dashboard');
        }
        
        Log::warning('Admin Login Failed', [
            'email' => $credentials['email'],
            'reason' => $admin ? 'invalid_password' : 'user_not_found'
        ]);
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Check observer login
    if ($request->has('observer_login')) {
        $observer = \App\Models\Observer::where('email', $credentials['email'])->first();
        
        if ($observer && \Illuminate\Support\Facades\Hash::check($credentials['password'], $observer->password)) {
            $request->session()->put('observer_user', $observer);
            $request->session()->regenerate();
            return redirect()->route('observer.dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Check candidate login
    if ($request->has('candidate_login')) {
        $candidate = \App\Models\CandidateUser::where('email', $credentials['email'])->first();
        
        if ($candidate && \Illuminate\Support\Facades\Hash::check($credentials['password'], $candidate->password)) {
            $request->session()->put('candidate_user', $candidate);
            $request->session()->regenerate();
            return redirect()->route('candidate.dashboard');
        }
        
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Regular voter login
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
});
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    // Check if admin logout
    if ($request->session()->has('admin_user')) {
        $request->session()->forget('admin_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
    
    // Regular user logout
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');

// Redirect to unified voter registration
Route::get('/register', function () {
    return redirect()->route('voter.register');
})->name('register');

// Registration Flow - Redirect to voter routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', function () {
        return redirect()->route('voter.login');
    })->name('login');
    
    Route::get('/register', function () {
        return redirect()->route('voter.register');
    })->name('register');
    
    Route::get('/register/step2', function () {
        return redirect()->route('voter.register');
    })->name('register.step2');
    
    Route::get('/register/step3', function () {
        return redirect()->route('voter.register');
    })->name('register.step3');
    
    Route::get('/registration-complete', function () {
        return redirect()->route('voter.register');
    })->name('registration-complete');


    
    // Admin Login
    Route::get('/admin/login', function () {
        return view('auth.admin-login');
    })->name('admin.login');
    
    // Logout
    Route::post('/logout', function () {
        $authService = app(\App\Services\Auth\AuthService::class);
        $authService->logout();
        return redirect()->route('home');
    })->name('logout')->middleware(['auth', '\App\Http\Middleware\VerifyCsrfToken']);
});
