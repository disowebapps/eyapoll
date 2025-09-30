<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backward Compatibility Routes
|--------------------------------------------------------------------------
| These routes maintain compatibility with existing views
*/

// Auth routes compatibility
Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', fn() => view('auth.login'))->name('login');
    Route::get('/register', fn() => view('auth.register-step1'))->name('register');
});

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');

// Public routes without prefix for compatibility
Route::get('/verify-receipt/{hash?}', [\App\Http\Controllers\ReceiptController::class, 'verify'])->name('verify-receipt');

// Legacy vote routes for backward compatibility - moved to voter.php

// Backward compatibility routes (without public. prefix) - moved to public.php