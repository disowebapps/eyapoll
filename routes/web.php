<?php

/*
|--------------------------------------------------------------------------
| Main Web Routes - Route Aggregator
|--------------------------------------------------------------------------
| This file includes all route modules for better organization
*/

// Include route modules
require __DIR__.'/public.php';

// Public candidate list route
Route::get('/elections/{election}/candidates', [\App\Http\Controllers\PublicCandidatesController::class, 'index'])
    ->name('election.candidates');
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/voter.php';
require __DIR__.'/candidate.php';
require __DIR__.'/voting.php';
require __DIR__.'/security.php';
require __DIR__.'/compatibility.php';

// Debug route
Route::get('/livewire-debug', function () {
    return view('livewire-debug');
})->middleware('auth:web');
Route::get('/debug-path-check', function () {
    require_once __DIR__ . '/debug_path.php';
});