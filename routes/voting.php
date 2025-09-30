<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Voting\VotingController;

/*
|--------------------------------------------------------------------------
| Voting API Routes - Authenticated Users Only
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'throttle:60,1'])->prefix('api/vote')->name('vote.')->group(function () {
    Route::post('/authorize/{election}', [VotingController::class, 'requestAuthorization'])->name('authorize');
    Route::post('/cast', [VotingController::class, 'castVote'])->name('cast');
    Route::post('/activity', [VotingController::class, 'trackActivity'])->name('activity');
    Route::get('/status/{authorization}', [VotingController::class, 'getAuthorizationStatus'])->name('status');
});