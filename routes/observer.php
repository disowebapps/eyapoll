<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Observer Routes
|--------------------------------------------------------------------------
*/

Route::prefix('observer')->name('observer.')->group(function () {
    // Observer Login (public)
    Route::get('/login', function () {
        return view('auth.observer-login');
    })->name('login');

    // POST handled by Livewire

    // Observer Logout
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::guard('observer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('observer.login');
    })->name('logout');

    // Protected Observer Routes
    Route::middleware('observer.auth')->group(function () {
        Route::get('/dashboard', function () {
            return view('observer.dashboard');
        })->name('dashboard');
        
        Route::get('/profile', \App\Livewire\Observer\Profile::class)->name('profile');

        Route::get('/audit-logs', function () {
            return view('observer.audit-logs');
        })->name('audit-logs');

        Route::get('/election/{election}/results', function (\App\Models\Election\Election $election) {
            return view('observer.election-results-livewire', ['electionId' => $election->id]);
        })->name('election-results');

        Route::get('/election/{election}/positions', function (\App\Models\Election\Election $election) {
            return view('observer.election-positions', ['electionId' => $election->id]);
        })->name('election-positions');

        Route::get('/election/{election}/voter-register', function (\App\Models\Election\Election $election) {
            return view('observer.voter-register', ['electionId' => $election->id]);
        })->name('voter-register');

        Route::get('/elections', function () {
            return view('observer.elections');
        })->name('elections');

        Route::get('/results', function () {
            return view('observer.election-results-list');
        })->name('results');

        Route::get('/election/{election}/details', function (\App\Models\Election\Election $election) {
            return view('observer.election-details', compact('election'));
        })->name('election-details');

        Route::get('/candidate/{candidate}/profile', function (\App\Models\Candidate\Candidate $candidate) {
            return view('observer.candidate-profile', ['candidateId' => $candidate->id]);
        })->name('candidate-profile');

        Route::get('/position/{position}/details', function (\App\Models\Election\Position $position) {
            return view('observer.position-details', ['positionId' => $position->id]);
        })->name('position-details');

        Route::get('/statistics', function () {
            return view('observer.statistics');
        })->name('statistics');

        Route::get('/submit-alert', function () {
            return view('observer.submit-alert');
        })->name('submit-alert');

        // Export routes
        Route::get('/audit-logs/export', [App\Http\Controllers\Observer\AuditLogController::class, 'export'])
            ->name('audit-logs.export');
        Route::get('/election/{election}/export/{format}', [App\Http\Controllers\Observer\ElectionResultsController::class, 'export'])
            ->name('election-results.export')
            ->where('format', 'csv|excel');
    });
});