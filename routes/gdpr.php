<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GDPRController;

/*
|--------------------------------------------------------------------------
| GDPR Compliance Routes
|--------------------------------------------------------------------------
|
| Routes for handling GDPR data subject rights including data export,
| data deletion, and data portability requests.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Data export requests
    Route::post('/gdpr/export', [GDPRController::class, 'requestExport'])
        ->name('gdpr.export.request');

    Route::get('/gdpr/export/{uuid}/download', [GDPRController::class, 'downloadExport'])
        ->name('gdpr.export.download');

    Route::get('/gdpr/export/status', [GDPRController::class, 'exportStatus'])
        ->name('gdpr.export.status');

    // Data deletion requests
    Route::post('/gdpr/delete', [GDPRController::class, 'requestDeletion'])
        ->name('gdpr.delete.request');

    Route::get('/gdpr/delete/status', [GDPRController::class, 'deletionStatus'])
        ->name('gdpr.delete.status');

    // Data portability
    Route::get('/gdpr/data', [GDPRController::class, 'getData'])
        ->name('gdpr.data');

    // Consent management
    Route::get('/gdpr/consent', [GDPRController::class, 'getConsentStatus'])
        ->name('gdpr.consent.status');

    Route::post('/gdpr/consent', [GDPRController::class, 'updateConsent'])
        ->name('gdpr.consent.update');
});

// Admin routes for GDPR management
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/gdpr')->group(function () {
    Route::get('/requests', [GDPRController::class, 'getRequests'])
        ->name('admin.gdpr.requests');

    Route::post('/export/{user}', [GDPRController::class, 'adminExportUserData'])
        ->name('admin.gdpr.export');

    Route::post('/delete/{user}', [GDPRController::class, 'adminDeleteUserData'])
        ->name('admin.gdpr.delete');

    Route::get('/stats', [GDPRController::class, 'getGDPRStats'])
        ->name('admin.gdpr.stats');
});