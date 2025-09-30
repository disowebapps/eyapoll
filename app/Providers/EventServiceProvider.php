<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use App\Listeners\LogLoginAttempt;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Login::class => [
            LogLoginAttempt::class . '@handleLogin',
        ],
        Failed::class => [
            LogLoginAttempt::class . '@handleFailed',
        ],
    ];

    public function boot(): void
    {
        //
    }
}