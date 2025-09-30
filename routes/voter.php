<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Voter Routes
|--------------------------------------------------------------------------
*/

Route::prefix('voter')->name('voter.')->group(function () {
    // Voter Login (public)
    Route::get('/login', function () {
        return view('auth.voter-login');
    })->name('login');

    // Voter MFA Verification
    Route::get('/mfa-verify/{userId}', function ($userId) {
        return view('auth.voter-mfa-verify', ['userId' => $userId]);
    })->name('mfa.verify');

    // Voter Registration
    Route::get('/register', function () {
        return view('auth.register-step1');
    })->name('register');
    
    Route::get('/register/step2', function () {
        return view('auth.register-step2');
    })->name('register.step2');
    
    Route::get('/register/step3', function () {
        return view('auth.register-step3');
    })->name('register.step3');
    
    Route::get('/register/step4', function () {
        return view('auth.register-step4');
    })->name('register.step4');
    
    Route::get('/registration-complete', function () {
        return view('auth.registration-complete');
    })->name('registration-complete');

    // POST handled by Livewire

    // Voter Logout
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('voter.login');
    })->name('logout');

    // Protected Voter Routes
    Route::middleware(['auth:web', 'kyc.access', 'force.https'])->group(function () {
        Route::get('/kyc-required', function () {
            return view('voter.kyc-required');
        })->name('kyc.required');
        
        Route::get('/dashboard', function () {
            return view('voter.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', function () {
            return view('voter.profile');
        })->name('profile');

        Route::get('/kyc', function () {
            return view('voter.kyc');
        })->name('kyc');
        
        Route::get('/elections', function () {
            return view('voter.elections');
        })->name('elections');
        
        // Voting routes
        Route::get('/vote/{election}', function (\App\Models\Election\Election $election) {
            return view('voter.vote', compact('election'));
        })->name('vote');
        
        Route::get('/receipt/{election}', function (\App\Models\Election\Election $election) {
            return view('voter.receipt', compact('election'));
        })->name('receipt');
        
        Route::get('/history', function () {
            return view('voter.history');
        })->name('history');

        // Appeal routes
        Route::resource('appeals', \App\Http\Controllers\AppealController::class)->parameters([
            'appeals' => 'appeal'
        ]);
        Route::get('/appeals/{appeal}/download/{document}', [\App\Http\Controllers\AppealController::class, 'downloadDocument'])
            ->name('appeals.download');

        Route::get('/document/{document}', [\App\Http\Controllers\Voter\DocumentController::class, 'view'])
            ->name('document.view');
    });
});