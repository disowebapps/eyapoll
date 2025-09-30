<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessNotificationQueue as ProcessNotificationQueueJob;
use App\Enums\Notification\NotificationChannel;
use App\Models\NotificationQueue;
use App\Services\Notification\NotificationService;

class ProcessNotificationQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-queue
                            {--limit=50 : Maximum number of notifications to process}
                            {--channel= : Process only notifications for specific channel (email, sms, in_app)}
                            {--sync : Process synchronously instead of queuing the job}
                            {--stats : Show queue statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the notification queue to send pending notifications';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $limit = (int) $this->option('limit');
        $channelOption = $this->option('channel');
        $sync = $this->option('sync');
        $showStats = $this->option('stats');

        // Show statistics if requested
        if ($showStats) {
            $this->showQueueStatistics();
            return;
        }

        // Validate channel option
        $channel = null;
        if ($channelOption) {
            try {
                $channel = NotificationChannel::from($channelOption);
            } catch (\ValueError $e) {
                $this->error("Invalid channel: {$channelOption}. Valid channels: email, sms, in_app");
                return 1;
            }
        }

        $this->info('Processing notification queue...');
        $this->info("Limit: {$limit}");
        if ($channel) {
            $this->info("Channel filter: {$channel->value}");
        }
        if ($sync) {
            $this->info('Processing mode: Synchronous');
        } else {
            $this->info('Processing mode: Asynchronous (queued job)');
        }

        if ($sync) {
            // Process synchronously
            $job = new ProcessNotificationQueueJob($limit, $channel);
            $job->handle($notificationService);

            $this->info('Notification queue processing completed synchronously.');
        } else {
            // Dispatch the job to the queue
            ProcessNotificationQueueJob::dispatch($limit, $channel);

            $this->info('Notification queue processing job dispatched to queue.');
            $this->info('Use `php artisan queue:work` to process the job.');
        }

        return 0;
    }

    /**
     * Show queue statistics
     */
    private function showQueueStatistics()
    {
        $this->info('Notification Queue Statistics');
        $this->line('================================');

        // Total counts
        $total = NotificationQueue::count();
        $available = NotificationQueue::available()->count();
        $reserved = NotificationQueue::reserved()->count();
        $failed = NotificationQueue::failed()->count();

        $this->line("Total queue items: <comment>{$total}</comment>");
        $this->line("Available for processing: <info>{$available}</info>");
        $this->line("Currently reserved: <warning>{$reserved}</warning>");
        $this->line("Permanently failed: <error>{$failed}</error>");

        // Channel breakdown
        $this->newLine();
        $this->info('By Channel:');
        foreach (NotificationChannel::cases() as $channel) {
            $count = NotificationQueue::channel($channel)->count();
            if ($count > 0) {
                $this->line("  {$channel->label()}: {$count}");
            }
        }

        // Priority breakdown
        $this->newLine();
        $this->info('By Priority:');
        $priorities = ['low', 'normal', 'high', 'urgent'];
        foreach ($priorities as $priority) {
            $count = NotificationQueue::where('priority', $priority)->count();
            if ($count > 0) {
                $this->line("  " . ucfirst($priority) . ": {$count}");
            }
        }

        // Recent activity
        $this->newLine();
        $this->info('Recent Activity (last 24 hours):');
        $recentProcessed = NotificationQueue::where('updated_at', '>=', now()->subDay())
            ->whereNotNull('reserved_at')
            ->count();
        $recentFailed = NotificationQueue::where('failed_at', '>=', now()->subDay())->count();

        $this->line("Items processed: <info>{$recentProcessed}</info>");
        $this->line("Items failed: <error>{$recentFailed}</error>");

        // Queue health
        $this->newLine();
        $this->info('Queue Health:');
        $oldItems = NotificationQueue::where('created_at', '<', now()->subDays(7))->count();
        $stuckItems = NotificationQueue::where('reserved_at', '<', now()->subHours(1))
            ->whereNull('failed_at')
            ->count();

        if ($oldItems > 0) {
            $this->warn("{$oldItems} items older than 7 days");
        }
        if ($stuckItems > 0) {
            $this->warn("{$stuckItems} items stuck in reserved state");
        }

        if ($oldItems === 0 && $stuckItems === 0) {
            $this->info('Queue appears healthy');
        }
    }
}
