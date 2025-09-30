<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/


Route::prefix('admin')->name('admin.')->middleware(['web'])->group(function () {
    // Admin Login (public)
    Route::get('/login', function () {
        return view('auth.admin-login');
    })->name('login')->middleware('throttle:10,1');

    // Password reset
    Route::get('/forgot-password', function () {
        return view('auth.admin-forgot-password');
    })->name('password.request');

    // POST handled by Livewire

    // Admin Logout
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    })->name('logout');

    // Protected Admin Routes
    Route::middleware(['admin.auth'])->group(function () {
 

        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        
        
        // Candidate Management
        Route::prefix('candidates')->name('candidates.')->group(function () {
            Route::get('/', function () {
                return view('admin.candidates.index');
            })->name('index');
            
            Route::get('/pending', function () {
                return view('admin.candidates.pending');
            })->name('pending');
            
            Route::get('/{candidate}', [\App\Http\Controllers\Admin\CandidateController::class, 'show'])->name('show');
            Route::get('/{candidate}/edit', [\App\Http\Controllers\Admin\CandidateController::class, 'edit'])->name('edit');
            Route::put('/{candidate}', [\App\Http\Controllers\Admin\CandidateController::class, 'update'])->name('update');
            
            Route::post('/{candidate}/approve', [\App\Http\Controllers\Admin\CandidateController::class, 'approve'])->name('approve');
            Route::post('/{candidate}/reject', [\App\Http\Controllers\Admin\CandidateController::class, 'reject'])->name('reject');
            Route::post('/{candidate}/suspend', [\App\Http\Controllers\Admin\CandidateController::class, 'suspend'])->name('suspend');
            Route::post('/{candidate}/unsuspend', [\App\Http\Controllers\Admin\CandidateController::class, 'unsuspend'])->name('unsuspend');
            

            Route::get('/{candidate}/payment-proof/{proof}/download', [\App\Http\Controllers\Admin\CandidateController::class, 'downloadPaymentProof'])->name('payment.proof.download');
        });

        // Observer Management
        Route::prefix('observers')->name('observers.')->group(function () {
            Route::get('/', function () {
                return view('admin.observers.index');
            })->name('index');
            
            Route::get('/{observer}', [\App\Http\Controllers\Admin\ObserverController::class, 'show'])->name('show');
            Route::get('/{observer}/edit', [\App\Http\Controllers\Admin\ObserverController::class, 'edit'])->name('edit');
            Route::put('/{observer}', [\App\Http\Controllers\Admin\ObserverController::class, 'update'])->name('update');
        });

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', function () {
                return view('admin.reports.index');
            })->name('index');
            
            Route::get('/elections', function () {
                return view('admin.reports.elections');
            })->name('elections');
            
            Route::get('/audit', function () {
                return view('admin.reports.audit');
            })->name('audit');
        });

        // System Maintenance
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/backup', function () {
                return view('admin.system.backup');
            })->name('backup');
            
            Route::get('/logs', function () {
                return view('admin.system.logs');
            })->name('logs');
        });

        // Election Management
        Route::prefix('elections')->name('elections.')->group(function () {
            Route::get('/', function () {
                return view('admin.elections.index');
            })->name('index');

            Route::get('/create', function () {
                return view('admin.elections.create-new');
            })->name('create');

            Route::get('/{election}', function ($electionId) {
                return view('admin.elections.management', compact('electionId'));
            })->name('show');

            Route::get('/{election}/edit', function ($electionId) {
                return view('admin.elections.edit', compact('electionId'));
            })->name('edit');

            Route::get('/{election}/results', function ($electionId) {
                return view('admin.elections.results', compact('electionId'));
            })->name('results');

            Route::get('/{election}/simple-results', function ($electionId) {
                return view('admin.elections.simple-results', compact('electionId'));
            })->name('simple-results');

            Route::get('/{election}/eligible-voters', function ($electionId) {
                return view('admin.elections.eligible-voters', compact('electionId'));
            })->name('eligible-voters');

            Route::get('/{election}/voter-register', [\App\Http\Controllers\Admin\VoterRegisterViewController::class, 'index'])->name('voter-register.view');
            Route::post('/{election}/voter-register/publish', [\App\Http\Controllers\Admin\VoterRegisterController::class, 'publish'])->name('voter-register.publish');
            Route::post('/{election}/voter-register/extend', [\App\Http\Controllers\Admin\VoterRegisterController::class, 'extend'])->name('voter-register.extend');
            Route::post('/{election}/voter-register/restart', [\App\Http\Controllers\Admin\VoterRegisterController::class, 'restart'])->name('voter-register.restart');

            Route::post('/{election}/candidate-register/set', [\App\Http\Controllers\Admin\CandidateRegisterController::class, 'setPeriod'])->name('candidate-register.set');
            Route::post('/{election}/candidate-register/extend', [\App\Http\Controllers\Admin\CandidateRegisterController::class, 'extend'])->name('candidate-register.extend');
            Route::post('/{election}/candidate-register/restart', [\App\Http\Controllers\Admin\CandidateRegisterController::class, 'restart'])->name('candidate-register.restart');
            Route::post('/{election}/candidate-list/publish', [\App\Http\Controllers\Admin\CandidateRegisterController::class, 'publishList'])->name('candidate-list.publish');

            Route::get('/{election}/results/export/{format}', function ($electionId, $format) {
                // Handle export logic here or delegate to controller
                $election = \App\Models\Election\Election::findOrFail($electionId);

                // Check if admin can access this election
                if (!Auth::guard('admin')->check()) {
                    abort(403);
                }

                // Generate filename
                $filename = 'election_' . $election->id . '_results_' . now()->format('Y_m_d_H_i_s');

                switch ($format) {
                    case 'csv':
                        return \App\Services\Export\ElectionResultsExportService::exportCsv($election, $filename);
                    case 'excel':
                        return \App\Services\Export\ElectionResultsExportService::exportExcel($election, $filename);
                    case 'audit':
                        return \App\Services\Export\ElectionResultsExportService::exportAudit($election, $filename);
                    default:
                        abort(400, 'Invalid export format');
                }
            })->name('election-results.export');
        });

        // Notification Management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', function () {
                return view('admin.notifications');
            })->name('index');

            Route::get('/logs', function () {
                return view('admin.notification-logs');
            })->name('logs');
        });

        // Voter Register
        Route::get('/voter-register', function () {
            return view('admin.voter-register');
        })->name('voter-register');

        // Admin Management
        Route::get('/admins', function () {
            return view('admin.admin-management');
        })->name('admins');

        // Document Review
        Route::get('/documents', function () {
            \Illuminate\Support\Facades\Log::info('Route admin.documents.review accessed', [
                'user_id' => auth('admin')->id(),
                'user_email' => auth('admin')->user()?->email,
                'is_super_admin' => auth('admin')->user()?->is_super_admin,
            ]);
            return view('admin.document-review');
        })->name('documents.review');

        // Profile
        Route::get('/profile', function () {
            return view('admin.profile');
        })->name('profile');
        
        // Settings
        Route::get('/settings', function () {
            return view('admin.settings');
        })->name('settings');
        
        // Emergency Controls (Super Admin Only)
        Route::get('/emergency', function () {
            return view('admin.emergency-controls');
        })->name('emergency')->middleware('super.admin');
        

        // User Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', function () {
                return view('admin.users.index');
            })->name('index');

            Route::get('/create', function () {
                return view('admin.users.create');
            })->name('create');

            Route::get('/{user}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('show');

            Route::get('/pending', function () {
                return view('admin.users.pending');
            })->name('pending');
        });
        
        // Voter Accreditation
        Route::get('/accreditation', function () {
            return view('admin.voter-accreditation');
        })->name('accreditation');
        
        // Token Monitor
        Route::get('/token-monitor', function () {
            return view('admin.token-monitor');
        })->name('token-monitor');

        // KYC Document Review
        Route::prefix('kyc')->name('kyc.')->middleware('sensitive.operation')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KycReviewController::class, 'index'])->name('index');
            Route::get('/review/{document}', [\App\Http\Controllers\Admin\KycReviewController::class, 'show'])->name('review');
            Route::post('/approve/{document}', [\App\Http\Controllers\Admin\KycReviewController::class, 'approve'])->name('approve');
            Route::post('/reject/{document}', [\App\Http\Controllers\Admin\KycReviewController::class, 'reject'])->name('reject');
            Route::post('/log-click', [\App\Http\Controllers\Admin\KycReviewController::class, 'logClick'])->name('log-click');
        });

        // Legacy route for backward compatibility
        Route::get('/kyc-review', function () {
            return redirect()->route('admin.kyc.index');
        })->name('kyc.legacy');
        
        // Document Viewing
        Route::get('/document/{document}', [\App\Http\Controllers\Admin\DocumentController::class, 'view'])
            ->name('document.view');

        // User Approvals
        Route::get('/user-approvals', function () {
            return view('admin.user-approvals');
        })->name('user-approvals');

        // Observer Alerts
        Route::get('/observer-alerts', function () {
            return view('admin.observer-alerts');
        })->name('observer-alerts');

        // Appeal Management
        Route::prefix('appeals')->name('appeals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AppealAdminController::class, 'index'])->name('index');
            Route::get('/{appeal}', [\App\Http\Controllers\Admin\AppealAdminController::class, 'show'])->name('show');
            Route::post('/{appeal}/assign', [\App\Http\Controllers\Admin\AppealAdminController::class, 'assign'])->name('assign');
            Route::post('/{appeal}/status', [\App\Http\Controllers\Admin\AppealAdminController::class, 'updateStatus'])->name('update-status');
            Route::post('/{appeal}/escalate', [\App\Http\Controllers\Admin\AppealAdminController::class, 'escalate'])->name('escalate');
            Route::post('/bulk-assign', [\App\Http\Controllers\Admin\AppealAdminController::class, 'bulkAssign'])->name('bulk-assign');
            Route::get('/statistics', [\App\Http\Controllers\Admin\AppealAdminController::class, 'statistics'])->name('statistics');
            Route::get('/document/{document}/download', [\App\Http\Controllers\Admin\AppealAdminController::class, 'downloadDocument'])->name('document.download');
            Route::post('/document/{document}/review', [\App\Http\Controllers\Admin\AppealAdminController::class, 'reviewDocument'])->name('document.review');
        });

        // Analytics
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/elections', [\App\Http\Controllers\Admin\ElectionAnalyticsController::class, 'index'])->name('elections');
            Route::post('/elections/refresh', [\App\Http\Controllers\Admin\ElectionAnalyticsController::class, 'refresh'])->name('elections.refresh');
            Route::get('/security', [\App\Http\Controllers\Admin\SecurityAnalyticsController::class, 'index']);
            Route::get('/compliance', [\App\Http\Controllers\Admin\ComplianceController::class, 'index']);
            Route::get('/system-health', [\App\Http\Controllers\Admin\SystemHealthController::class, 'index']);
        });

        // Alert Management (AJAX)
        Route::get('/alerts', [\App\Http\Controllers\Admin\AlertController::class, 'index']);
        Route::post('/alerts/{alert}/read', [\App\Http\Controllers\Admin\AlertController::class, 'markAsRead']);
        Route::post('/alerts/mark-all-read', [\App\Http\Controllers\Admin\AlertController::class, 'markAllRead']);
    });
});