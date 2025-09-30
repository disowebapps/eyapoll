<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
            Route::middleware('web')
                ->group(base_path('routes/voter.php'));
            Route::middleware('web')
                ->group(base_path('routes/candidate.php'));
            Route::middleware('web')
                ->group(base_path('routes/observer.php'));
            Route::middleware('web')
                ->group(base_path('routes/livewire-test.php'));
            Route::middleware('web')
                ->group(base_path('routes/livewire.php'));
        },
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware for security
        $middleware->use([
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CheckBlockedIp::class,
        ]);
        
        $middleware->validateCsrfTokens(except: [
            'livewire/message/*',
        ]);

        $middleware->alias([
            'kyc.access' => \App\Http\Middleware\EnsureKycAccess::class,
            'voting.eligible' => \App\Http\Middleware\EnsureVotingEligibility::class,
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'observer.auth' => \App\Http\Middleware\ObserverAuth::class,
            'candidate.auth' => \App\Http\Middleware\CandidateAuth::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'throttle.login' => \App\Http\Middleware\ThrottleLoginAttempts::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'blocked.ip' => \App\Http\Middleware\CheckBlockedIp::class,
            'signed' => \App\Http\Middleware\ValidateSignedUrl::class,
            'super.admin' => \App\Http\Middleware\SuperAdminOnly::class,
            'sensitive.operation' => \App\Http\Middleware\SensitiveOperationMiddleware::class,
            'force.https' => \App\Http\Middleware\ForceHttps::class,
            'candidate.deadline' => \App\Http\Middleware\CheckCandidateApplicationDeadline::class,
            'multi.guard.auth' => \App\Http\Middleware\MultiGuardAuth::class,
        ]);


    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
