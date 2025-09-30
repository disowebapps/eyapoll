<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Enums\Auth\UserRole;

/*
|--------------------------------------------------------------------------
| Dashboard Routes - Role-Based Access
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Main dashboard with role routing
    Route::get('/dashboard', function () {
        $user = Auth::user();
        
        return match($user->role) {
            UserRole::CANDIDATE => redirect()->route('candidate.dashboard'),
            UserRole::ADMIN => redirect()->route('admin.dashboard'),
            UserRole::OBSERVER => redirect()->route('observer.dashboard'),
            default => redirect()->route('voter.dashboard'),
        };
    })->name('dashboard');
    
    // Voter Dashboard
    Route::prefix('voter')->name('voter.')->group(function () {
        Route::get('/dashboard', fn() => view('voter.dashboard'))->name('dashboard');
    });
    
    // Candidate Dashboard
    Route::prefix('candidate')->name('candidate.')->group(function () {
        Route::get('/dashboard', function () {
            if (Auth::user()->role !== UserRole::CANDIDATE) {
                return redirect()->route('dashboard');
            }
            return view('candidate.dashboard');
        })->name('dashboard');
    });
    
    // Observer Dashboard
    Route::prefix('observer')->name('observer.')->group(function () {
        Route::get('/dashboard', function () {
            if (Auth::user()->role !== UserRole::OBSERVER) {
                return redirect()->route('dashboard');
            }
            return view('observer.dashboard');
        })->name('dashboard');
    });
    
    // Admin Dashboard
    Route::prefix('admin')->name('admin.')->middleware(['auth:admin'])->group(function () {
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
        Route::get('/members', fn() => view('admin.members.index'))->name('members.index');
        
        Route::post('/logout', function () {
            auth('admin')->logout();
            return redirect()->route('home');
        })->name('logout');
    });
});