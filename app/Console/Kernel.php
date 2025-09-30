<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SecureElectionMonitor::class,
        Commands\CreateAdminUser::class,
        Commands\ProcessNotificationQueue::class,
        Commands\ResetAdminPasswords::class,
        Commands\PopulateElectionCandidates::class,
        Commands\MonitorVoterRegistration::class,
        Commands\AutoResumeRegistration::class,
        Commands\AutoArchiveElections::class,
        Commands\ProcessUserStatusUpdates::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Existing schedules
        $schedule->command('elections:monitor')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        $schedule->command('notifications:process-queue')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        $schedule->command('voter:monitor-registration')
                 ->hourly()
                 ->withoutOverlapping();

        $schedule->command('voter:auto-resume')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping();

        $schedule->command('users:process-status-updates')
                 ->hourly()
                 ->withoutOverlapping();

        // New metrics collection
        $schedule->job(\App\Jobs\CollectSystemMetrics::class)
                 ->everyMinute()
                 ->withoutOverlapping();

        // Alert monitoring
        $schedule->call(function () {
            app(\App\Services\Admin\AlertService::class)->checkThresholds();
        })->everyFiveMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}