<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessUserStatusUpdates as ProcessUserStatusUpdatesJob;
use App\Services\Auth\UserStatusService;
use App\Models\User;
use App\Enums\Auth\UserStatus;

class ProcessUserStatusUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:process-status-updates
                            {--sync : Process synchronously instead of queuing the job}
                            {--stats : Show user status statistics}
                            {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automatic user status updates based on time conditions';

    /**
     * Execute the console command.
     */
    public function handle(UserStatusService $userStatusService)
    {
        $sync = $this->option('sync');
        $showStats = $this->option('stats');
        $dryRun = $this->option('dry-run');

        // Show statistics if requested
        if ($showStats) {
            $this->showUserStatusStatistics();
            return;
        }

        // Show dry run if requested
        if ($dryRun) {
            $this->showDryRunResults($userStatusService);
            return;
        }

        $this->info('Processing user status updates...');

        if ($sync) {
            $this->info('Processing mode: Synchronous');
            $job = new ProcessUserStatusUpdatesJob();
            $job->handle($userStatusService);
            $this->info('User status update processing completed synchronously.');
        } else {
            $this->info('Processing mode: Asynchronous (queued job)');
            ProcessUserStatusUpdatesJob::dispatch();
            $this->info('User status update job dispatched to queue.');
            $this->info('Use `php artisan queue:work` to process the job.');
        }

        return 0;
    }

    /**
     * Show user status statistics
     */
    private function showUserStatusStatistics()
    {
        $this->info('User Status Statistics');
        $this->line('======================');

        // Status counts
        $statusCounts = [];
        foreach (UserStatus::cases() as $status) {
            $statusCounts[$status->value] = User::where('status', $status)->count();
        }

        $this->line('Current Status Distribution:');
        foreach ($statusCounts as $status => $count) {
            $this->line("  {$status}: {$count}");
        }

        // Users requiring automatic updates
        $this->newLine();
        $this->info('Users Requiring Automatic Updates:');

        $now = now();

        $temporaryHoldExpiring = User::where('status', UserStatus::TEMPORARY_HOLD)
            ->where('hold_until', '<=', $now)
            ->count();
        $this->line("  Temporary holds expiring: {$temporaryHoldExpiring}");

        $expiringUsers = User::whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
            ->where('expiry_date', '<=', $now)
            ->count();
        $this->line("  Users with expired status: {$expiringUsers}");

        $renewalOverdue = User::where('status', UserStatus::RENEWAL_REQUIRED)
            ->where('renewal_deadline', '<=', $now)
            ->count();
        $this->line("  Renewal deadlines passed: {$renewalOverdue}");

        $totalRequiringUpdate = $temporaryHoldExpiring + $expiringUsers + $renewalOverdue;
        $this->line("  <comment>Total requiring updates: {$totalRequiringUpdate}</comment>");

        // Upcoming deadlines
        $this->newLine();
        $this->info('Upcoming Deadlines (next 7 days):');

        $nextWeek = now()->addDays(7);

        $upcomingHolds = User::where('status', UserStatus::TEMPORARY_HOLD)
            ->whereBetween('hold_until', [$now, $nextWeek])
            ->count();
        $this->line("  Temporary holds expiring: {$upcomingHolds}");

        $upcomingExpiries = User::whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
            ->whereBetween('expiry_date', [$now, $nextWeek])
            ->count();
        $this->line("  Status expiries: {$upcomingExpiries}");

        $upcomingRenewals = User::where('status', UserStatus::RENEWAL_REQUIRED)
            ->whereBetween('renewal_deadline', [$now, $nextWeek])
            ->count();
        $this->line("  Renewal deadlines: {$upcomingRenewals}");
    }

    /**
     * Show dry run results
     */
    private function showDryRunResults(UserStatusService $userStatusService)
    {
        $this->info('User Status Update Dry Run');
        $this->line('============================');

        $usersNeedingUpdate = User::where(function ($query) {
            $now = now();
            $query->where('status', UserStatus::TEMPORARY_HOLD)
                  ->where('hold_until', '<=', $now)
                  ->orWhere(function ($q) use ($now) {
                      $q->whereIn('status', [UserStatus::APPROVED, UserStatus::ACCREDITED])
                        ->where('expiry_date', '<=', $now);
                  })
                  ->orWhere(function ($q) use ($now) {
                      $q->where('status', UserStatus::RENEWAL_REQUIRED)
                        ->where('renewal_deadline', '<=', $now);
                  });
        })->get();

        if ($usersNeedingUpdate->isEmpty()) {
            $this->info('No users require status updates at this time.');
            return;
        }

        $this->warn("Found {$usersNeedingUpdate->count()} users that would be updated:");

        foreach ($usersNeedingUpdate as $user) {
            $newStatus = $userStatusService->checkForAutomaticUpdates($user);
            if ($newStatus) {
                $this->line("  User {$user->id} ({$user->email}): {$user->status->value} → {$newStatus->value}");
            }
        }

        $this->newLine();
        $this->comment('This is a dry run. No changes have been made.');
        $this->comment('Remove --dry-run flag to apply the updates.');
    }
}