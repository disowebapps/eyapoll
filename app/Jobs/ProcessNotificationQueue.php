<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationQueue;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationChannel;

class ProcessNotificationQueue implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $limit = 50,
        public ?NotificationChannel $channel = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        $processed = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        // Get queue items to process
        $query = NotificationQueue::available()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($this->limit);

        if ($this->channel) {
            $query->where('channel', $this->channel->value);
        }

        $queueItems = $query->get();

        Log::info('Starting notification queue processing', [
            'items_to_process' => $queueItems->count(),
            'channel_filter' => $this->channel?->value,
            'limit' => $this->limit,
        ]);

        foreach ($queueItems as $queueItem) {
            try {
                $processed++;

                // Reserve the queue item
                if (!$queueItem->reserve('notification-queue-processor-' . gethostname())) {
                    Log::warning('Failed to reserve queue item', [
                        'queue_item_id' => $queueItem->id,
                    ]);
                    continue;
                }

                // Get the notification
                $notification = $queueItem->notification;
                if (!$notification) {
                    Log::warning('Notification not found for queue item', [
                        'queue_item_id' => $queueItem->id,
                        'notification_id' => $queueItem->notification_id,
                    ]);
                    $queueItem->markAsFailed('Notification not found');
                    $failed++;
                    continue;
                }

                // Send the notification
                $result = $notificationService->sendImmediately($notification);

                if ($result['success']) {
                    $queueItem->markAsCompleted();
                    $successful++;

                    Log::info('Notification sent successfully', [
                        'notification_id' => $notification->id,
                        'queue_item_id' => $queueItem->id,
                        'channel' => $notification->channel->value,
                    ]);
                } else {
                    $errorMessage = $result['error'] ?? 'Unknown error';
                    $queueItem->markAsFailed($errorMessage);
                    $failed++;
                    $errors[] = [
                        'notification_id' => $notification->id,
                        'queue_item_id' => $queueItem->id,
                        'error' => $errorMessage,
                    ];

                    Log::warning('Notification sending failed', [
                        'notification_id' => $notification->id,
                        'queue_item_id' => $queueItem->id,
                        'channel' => $notification->channel->value,
                        'error' => $errorMessage,
                    ]);
                }

            } catch (\Exception $e) {
                $queueItem->markAsFailed($e->getMessage());
                $failed++;
                $errors[] = [
                    'queue_item_id' => $queueItem->id,
                    'error' => $e->getMessage(),
                ];

                Log::error('Exception during notification processing', [
                    'queue_item_id' => $queueItem->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        Log::info('Notification queue processing completed', [
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed,
            'channel_filter' => $this->channel?->value,
        ]);

        // Log summary if there were failures
        if (!empty($errors)) {
            Log::warning('Notification processing errors summary', [
                'total_errors' => count($errors),
                'errors' => array_slice($errors, 0, 10), // Log first 10 errors
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Notification queue processing job failed permanently', [
            'limit' => $this->limit,
            'channel' => $this->channel?->value,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['notification-queue'];

        if ($this->channel) {
            $tags[] = 'channel:' . $this->channel->value;
        }

        return $tags;
    }
}
