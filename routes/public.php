<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicMemberController;
use App\Http\Controllers\PublicCandidateController;

/*
|--------------------------------------------------------------------------
| Public Routes - No Authentication Required
|--------------------------------------------------------------------------
*/

// Home route (both with and without prefix)
Route::get('/', fn() => view('home'))->name('home');

Route::prefix('public')->name('public.')->group(function () {
    
    // Election Information
    Route::get('/results', [\App\Http\Controllers\PublicElectionResultsController::class, 'index'])->name('results');
    Route::get('/verify-vote', fn(\Illuminate\Http\Request $request) => 
        view('public.verify-vote', ['hash' => $request->query('hash')])
    )->name('verify-vote');
    Route::get('/verify-receipt', fn(\Illuminate\Http\Request $request) => 
        view('public.verify-receipt', ['hash' => $request->query('hash')])
    )->name('verify.receipt');
    Route::get('/voter-register', function() {
        return view('public.voter-register');
    })->name('voter-register');
    
    // System Information
    Route::get('/how-it-works', fn() => view('public.how-it-works'))->name('how-it-works');
    Route::get('/security', fn() => view('public.security'))->name('security');
    Route::get('/features', fn() => view('public.features'))->name('features');
    Route::get('/election-integrity', function() {
        $type = new class {
            public function label() { return 'General Election'; }
        };
        
        $status = new class {
            public function label() { return 'Completed'; }
        };
        
        $votes = new class {
            public function count() { return 0; }
        };
        
        $election = (object)[
            'title' => 'Sample Election',
            'description' => 'Demonstration election for integrity verification',
            'type' => $type,
            'status' => $status,
            'votes' => $votes
        ];
        
        $integrity = [
            'verified_votes' => 0,
            'integrity_percentage' => 100,
            'chain_valid' => true,
            'invalid_votes' => []
        ];
        
        return view('public.election-integrity', compact('election', 'integrity'));
    })->name('election-integrity');
    
    // Directory Services
    Route::controller(PublicMemberController::class)->group(function () {
        Route::get('/members', 'index')->name('members');
        Route::get('/executives', 'executives')->name('executives');
        Route::get('/members/search', 'search')->name('members.search');
        Route::get('/member/{id}', 'show')->name('member.profile');
    });
    
    Route::controller(PublicCandidateController::class)->group(function () {
        Route::get('/candidates', 'index')->name('candidates');
        Route::get('/candidate/{id}', 'show')->name('candidate');
    });
    
    // Legal & Support
    Route::get('/contact', fn() => view('public.contact'))->name('contact');
    Route::get('/help', fn() => view('public.help'))->name('help');
    Route::get('/about', fn() => view('public.about'))->name('about');
    Route::get('/privacy-policy', fn() => view('public.privacy-policy'))->name('privacy-policy');
    Route::get('/terms-of-service', fn() => view('public.terms-of-service'))->name('terms-of-service');
});