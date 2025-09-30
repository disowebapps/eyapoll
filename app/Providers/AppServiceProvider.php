<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for cache invalidation
        \App\Models\Election\Election::observe(\App\Observers\ElectionObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\VoterRegisterPublished::class,
            \App\Listeners\HandleVoterRegisterPublished::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\ElectionResultsPublished::class,
            \App\Listeners\HandleElectionResultsPublished::class
        );
    }
}
