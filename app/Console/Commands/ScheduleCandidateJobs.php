<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SendPaymentReminders;
use App\Jobs\EnforceApplicationDeadlines;

class ScheduleCandidateJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candidates:schedule-jobs {--force : Run jobs immediately instead of queuing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedule automated candidate management jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $this->info('Scheduling candidate management jobs...');

        if ($force) {
            $this->info('Running jobs immediately...');

            // Run payment reminders
            $this->info('Running payment reminders...');
            dispatch_sync(new SendPaymentReminders());

            // Run deadline enforcement
            $this->info('Running application deadline enforcement...');
            dispatch_sync(new EnforceApplicationDeadlines());

        } else {
            $this->info('Queueing jobs...');

            // Queue payment reminders
            SendPaymentReminders::dispatch();
            $this->info('✓ Payment reminders job queued');

            // Queue deadline enforcement
            EnforceApplicationDeadlines::dispatch();
            $this->info('✓ Application deadline enforcement job queued');
        }

        $this->info('All candidate jobs have been processed successfully!');
    }
}