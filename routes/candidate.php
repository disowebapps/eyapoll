<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Candidate Routes
|--------------------------------------------------------------------------
*/

Route::prefix('candidate')->name('candidate.')->group(function () {
    // Candidate Login (public)
    Route::get('/login', [App\Http\Controllers\Auth\CandidateAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\CandidateAuthController::class, 'login']);

    // Candidate Registration
    Route::get('/register', function () {
        return view('auth.register-step1');
    })->name('register');



    // Candidate Logout (redirect to voter logout)
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        return redirect()->route('voter.logout');
    })->name('logout');

    // Protected Candidate Routes (allow both voter and admin auth)
    Route::middleware('multi.guard.auth:web,admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('candidate.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', function () {
            return view('candidate.profile');
        })->name('profile');
        
        Route::get('/apply/{election}', function (\App\Models\Election\Election $election) {
            return view('candidate.apply', compact('election'));
        })->name('apply');
        
        Route::get('/application/{candidate}', function ($candidateId) {
            return view('candidate.application', compact('candidateId'));
        })->name('application');
        
        Route::get('/applications', function () {
            return view('candidate.applications');
        })->name('applications');
        
        // Voting routes (candidates can also vote)
        Route::get('/vote/{election}', function ($electionId) {
            return view('candidate.vote', compact('electionId'));
        })->name('vote');
        
        Route::get('/receipt/{election}', function ($electionId) {
            return view('candidate.receipt', compact('electionId'));
        })->name('receipt');

        // Results monitoring
        Route::get('/results/{election?}', function ($electionId = null) {
            return view('candidate.results', compact('electionId'));
        })->name('results');
    });
});