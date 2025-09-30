<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use App\Services\Candidate\CandidateService;
use App\Services\Election\ElectionService;

class AyapollServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CandidateService::class);
        $this->app->singleton(ElectionService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }




}