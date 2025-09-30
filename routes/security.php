<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Security & Protected Routes
|--------------------------------------------------------------------------
*/

// Signed URL protected document downloads
Route::middleware(['signed'])->group(function () {
    Route::get('/documents/{document}/download', function (\App\Models\Auth\IdDocument $document) {
        $path = storage_path('app/private/documents/' . $document->filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path, $document->original_filename);
    })->name('documents.download');

    // Secure file serving with encryption
    Route::get('/secure-files/{filename}', [\App\Http\Controllers\SecureFileController::class, 'view'])
        ->name('secure-files.view');
});

// Debug logging (development only)
Route::post('/debug-log', function(\Illuminate\Http\Request $request) {
    Log::info('JS Debug: ' . $request->message, $request->data ?? []);
    return response()->json(['status' => 'logged']);
})->middleware('web');

// Test routes (remove in production)
Route::get('/test-voting', fn() => view('test-voting'))->middleware('auth:web');