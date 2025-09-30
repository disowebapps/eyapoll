<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Models\Admin;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\EmailTemplate;
use App\Models\SmsTemplate;
use App\Models\InAppTemplate;
use App\Models\NotificationQueue;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationEventType;
use App\Enums\Notification\NotificationStatus;
use App\Enums\Notification\NotificationPriority;
use App\Services\Notification\Providers\EmailNotificationProvider;
use App\Services\Notification\Providers\SmsNotificationProvider;
use App\Services\Notification\Providers\InAppNotificationProvider;
use App\Services\Notification\Providers\NotificationProviderInterface;
use App\Jobs\Notifications\SendNotificationJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Notifications\Notifiable;

class NotificationService
{
    protected array $providers = [];

    public function __construct()
    {
        $this->registerProviders();
    }

    /**
     * Register notification providers
     */
    protected function registerProviders(): void
    {
        $this->providers = [
            NotificationChannel::EMAIL->value => new EmailNotificationProvider(),
            NotificationChannel::SMS->value => new SmsNotificationProvider(),
            NotificationChannel::IN_APP->value => new InAppNotificationProvider(),
        ];
    }

    /**
     * Get provider for a specific channel
     */
    public function getProvider(NotificationChannel $channel): ?NotificationProviderInterface
    {
        return $this->providers[$channel->value] ?? null;
    }

    /**
     * Get all available providers
     */
    public function getProviders(): Collection
    {
        return collect($this->providers);
    }

    /**
     * Send a notification to a notifiable model (User or Admin)
     */
    public function send(
        User|Admin $notifiable,
        string $eventType,
        array $data = [],
        string $channel = 'email'
    ): Notification {
        $channelEnum = NotificationChannel::from($channel);

        // Check if channel is enabled
        if (!$this->isChannelEnabled($channelEnum)) {
            throw new \InvalidArgumentException("Notification channel '{$channel}' is not enabled");
        }

        // Get template for this event and channel
        $template = $this->getTemplate($eventType, $channelEnum);

        if (!$template) {
            throw new \InvalidArgumentException("No template found for event '{$eventType}' and channel '{$channel}'");
        }

        // Create notification record
        $notificationData = array_merge($data, [
            'user_name' => $notifiable->full_name,
            'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
        ]);

        // Add template_id if template exists
        if ($template) {
            $notificationData['template_id'] = $template->id;
        }

        $notification = Notification::create([
            'uuid' => Str::uuid(),
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id,
            'type' => $eventType,
            'data' => $notificationData,
            'channel' => $channelEnum,
            'status' => NotificationStatus::PENDING,
        ]);

        // Queue the notification for delivery using the new queue system
        $this->queueForDelivery($notification);

        return $notification;
    }

    /**
     * Queue notification for delivery using the new queue system
     */
    public function queueForDelivery(
        Notification $notification,
        ?NotificationPriority $priority = null,
        ?\DateTime $delay = null
    ): NotificationQueue {
        $priority = $priority ?? NotificationPriority::NORMAL;

        $queueItem = NotificationQueue::create([
            'uuid' => Str::uuid(),
            'notification_id' => $notification->id,
            'channel' => $notification->channel->value,
            'recipient_id' => $notification->channel === NotificationChannel::IN_APP ? $notification->notifiable_id : null,
            'recipient_email' => $notification->channel === NotificationChannel::EMAIL ? $notification->notifiable->email : null,
            'recipient_phone' => $notification->channel === NotificationChannel::SMS ? ($notification->notifiable->phone_number ?? null) : null,
            'priority' => $priority->value,
            'max_retries' => $priority->maxRetries(),
            'available_at' => $delay ?? now(),
            'payload' => [
                'notification_uuid' => $notification->uuid,
                'event_type' => $notification->type,
                'notifiable_type' => $notification->notifiable_type,
                'notifiable_id' => $notification->notifiable_id,
            ],
        ]);

        return $queueItem;
    }

    /**
     * Send notification immediately using provider
     */
    public function sendImmediately(Notification $notification): array
    {
        $provider = $this->getProvider($notification->channel);

        if (!$provider) {
            throw new \InvalidArgumentException("No provider available for channel '{$notification->channel->value}'");
        }

        if (!$provider->isAvailable()) {
            throw new \RuntimeException("Provider for channel '{$notification->channel->value}' is not available");
        }

        $result = $provider->send($notification);

        // Update notification status based on result
        if ($result['success']) {
            $notification->update([
                'status' => NotificationStatus::SENT,
                'sent_at' => now(),
            ]);
        } else {
            $notification->update([
                'status' => NotificationStatus::FAILED,
                'failure_reason' => $result['error'] ?? 'Unknown error',
            ]);
        }

        return $result;
    }

    /**
     * Process queued notifications
     */
    public function processQueue(int $limit = 10): array
    {
        $processed = 0;
        $successful = 0;
        $failed = 0;

        $queueItems = NotificationQueue::available()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        foreach ($queueItems as $queueItem) {
            try {
                $queueItem->reserve('notification-service-' . gethostname());

                $notification = $queueItem->notification;
                $result = $this->sendImmediately($notification);

                if ($result['success']) {
                    $queueItem->markAsCompleted();
                    $successful++;
                } else {
                    $queueItem->markAsFailed($result['error'] ?? 'Delivery failed');
                    $failed++;
                }

                $processed++;

            } catch (\Exception $e) {
                Log::error('Queue processing failed', [
                    'queue_item_id' => $queueItem->id,
                    'error' => $e->getMessage(),
                ]);

                $queueItem->markAsFailed($e->getMessage());
                $failed++;
                $processed++;
            }
        }

        return [
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    /**
     * Send notification by event type with automatic channel selection
     */
    public function sendByEvent(
        NotificationEventType $eventType,
        User|Admin $notifiable,
        array $data = []
    ): array {
        $notifications = [];
        $channels = $eventType->defaultChannels();

        foreach ($channels as $channel) {
            if ($this->isChannelEnabled($channel)) {
                try {
                    $notification = $this->send($notifiable, $eventType->value, $data, $channel->value);
                    $notifications[] = $notification;
                } catch (\Exception $e) {
                    Log::warning('Failed to send notification', [
                        'event_type' => $eventType->value,
                        'channel' => $channel->value,
                        'notifiable_type' => get_class($notifiable),
                        'notifiable_id' => $notifiable->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $notifications;
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(
        array $notifiables,
        string $eventType,
        array $data = [],
        array $channels = ['email']
    ): array {
        $notifications = [];

        foreach ($notifiables as $notifiable) {
            foreach ($channels as $channel) {
                try {
                    $notifications[] = $this->send($notifiable, $eventType, $data, $channel);
                } catch (\Exception $e) {
                    Log::warning('Failed to send bulk notification', [
                        'notifiable_type' => get_class($notifiable),
                        'notifiable_id' => $notifiable->id,
                        'event_type' => $eventType,
                        'channel' => $channel,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $notifications;
    }

    /**
     * Queue notification for later delivery
     */
    public function queue(
        User|Admin $notifiable,
        string $eventType,
        array $data = [],
        string $channel = 'email',
        ?\DateTime $sendAt = null
    ): Notification {
        $notification = $this->send($notifiable, $eventType, $data, $channel);

        if ($sendAt) {
            SendNotificationJob::dispatch($notification)->delay($sendAt);
        }

        return $notification;
    }

    /**
     * Get notification template based on channel
     */
    public function getTemplate(string $eventType, NotificationChannel $channel)
    {
        return match($channel) {
            NotificationChannel::EMAIL => EmailTemplate::forEvent($eventType)->active()->first(),
            NotificationChannel::SMS => SmsTemplate::forEvent($eventType)->active()->first(),
            NotificationChannel::IN_APP => InAppTemplate::forEvent($eventType)->active()->first(),
            default => null,
        };
    }

    /**
     * Render notification content from template
     */
    public function renderTemplate($template, array $data): array
    {
        // Handle different template types
        if ($template instanceof EmailTemplate) {
            return $template->render($data);
        } elseif ($template instanceof SmsTemplate) {
            return [
                'message' => $template->render($data),
                'cost' => $template->estimated_cost,
            ];
        } elseif ($template instanceof InAppTemplate) {
            return $template->render($data);
        }

        // Fallback for old NotificationTemplate (if still used)
        if ($template instanceof NotificationTemplate) {
            $subject = $template->subject;
            $body = $template->body_template;

            // Replace template variables
            foreach ($data as $key => $value) {
                $placeholder = '{{ ' . $key . ' }}';
                $subject = str_replace($placeholder, $value, $subject);
                $body = str_replace($placeholder, $value, $body);
            }

            return [
                'subject' => $subject,
                'body' => $body,
            ];
        }

        throw new \InvalidArgumentException('Unsupported template type');
    }

    /**
     * Check if notification channel is enabled
     */
    public function isChannelEnabled(NotificationChannel $channel): bool
    {
        return match($channel) {
            NotificationChannel::EMAIL => config('ayapoll.notification_channels.email', true),
            NotificationChannel::SMS => config('ayapoll.notification_channels.sms', false),
            NotificationChannel::IN_APP => config('ayapoll.notification_channels.in_app', true),
        };
    }

    /**
     * Get enabled channels for an event
     */
    public function getEnabledChannelsForEvent(string $eventType): array
    {
        $configuredChannels = config("notifications.event_channels.{$eventType}", ['email']);
        $enabledChannels = [];

        foreach ($configuredChannels as $channel) {
            $channelEnum = NotificationChannel::from($channel);
            if ($this->isChannelEnabled($channelEnum)) {
                $enabledChannels[] = $channelEnum;
            }
        }

        return $enabledChannels;
    }

    /**
     * Mark notification as sent
     */
    public function markAsSent(Notification $notification): void
    {
        $notification->update([
            'status' => NotificationStatus::SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(Notification $notification, string $reason): void
    {
        $notification->update([
            'status' => NotificationStatus::FAILED,
            'failure_reason' => $reason,
            'retry_count' => $notification->retry_count + 1,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        if ($notification->channel === NotificationChannel::IN_APP) {
            $notification->update([
                'status' => NotificationStatus::READ,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Retry failed notification
     */
    public function retry(Notification $notification): void
    {
        if (!$notification->status->canRetry()) {
            throw new \InvalidArgumentException('Notification cannot be retried');
        }

        $maxRetries = config('notifications.delivery.max_retry_attempts', 3);
        
        if ($notification->retry_count >= $maxRetries) {
            throw new \InvalidArgumentException('Maximum retry attempts exceeded');
        }

        $notification->update([
            'status' => NotificationStatus::PENDING,
            'failure_reason' => null,
        ]);

        // Calculate delay based on retry count
        $backoffMultiplier = config('notifications.delivery.retry_backoff_multiplier', 2);
        $delayMinutes = $notification->retry_count * $backoffMultiplier;

        SendNotificationJob::dispatch($notification)->delay(now()->addMinutes($delayMinutes));
    }

    /**
     * Get notifiable model's unread notifications
     */
    public function getUnreadNotifications(User|Admin $notifiable, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Notification::whereRaw('notifiable_type = ? AND notifiable_id = ? AND channel = ? AND status = ?', [
                get_class($notifiable),
                $notifiable->id,
                NotificationChannel::IN_APP->value,
                NotificationStatus::SENT->value
            ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get notification statistics
     */
    public function getStatistics(): array
    {
        $total = Notification::count();
        $sent = Notification::where('status', NotificationStatus::SENT)->count();
        $failed = Notification::where('status', NotificationStatus::FAILED)->count();
        $pending = Notification::where('status', NotificationStatus::PENDING)->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $pending,
            'success_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Clean old notifications
     */
    public function cleanOldNotifications(): int
    {
        $retentionDays = config('notifications.delivery.failed_notification_retention_days', 7);
        
        $deleted = Notification::where('status', NotificationStatus::FAILED)
            ->where('created_at', '<', now()->subDays($retentionDays))
            ->delete();

        // Also clean old read in-app notifications
        $inAppRetentionDays = config('notifications.channels.in_app.retention_days', 30);
        
        $deletedInApp = Notification::where('channel', NotificationChannel::IN_APP)
            ->where('status', NotificationStatus::READ)
            ->where('read_at', '<', now()->subDays($inAppRetentionDays))
            ->delete();

        return $deleted + $deletedInApp;
    }

    /**
     * Send user approval notification
     */
    public function sendUserApprovalNotification(User $user): array
    {
        return $this->sendByEvent(
            NotificationEventType::USER_APPROVED,
            $user,
            [
                'user_name' => $user->full_name,
                'approval_date' => now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Send candidate approval notification
     */
    public function sendCandidateApprovalNotification($candidate): array
    {
        return $this->sendByEvent(
            NotificationEventType::CANDIDATE_APPROVED,
            $candidate->user,
            [
                'candidate_name' => $candidate->user->full_name,
                'position_title' => $candidate->position->title ?? 'Unknown Position',
                'approval_date' => now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Send user rejection notification
     */
    public function sendUserRejectionNotification(User $user, string $reason): array
    {
        return $this->sendByEvent(
            NotificationEventType::USER_REJECTED,
            $user,
            [
                'user_name' => $user->full_name,
                'rejection_reason' => $reason,
                'rejection_date' => now()->format('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * Send election started notification
     */
    public function sendElectionStartedNotification($election): array
    {
        // Get all eligible voters
        $voters = User::where('status', 'approved')
            ->whereHas('voteTokens', function ($query) use ($election) {
                $query->where('election_id', $election->id);
            })
            ->get();

        $notifications = [];
        foreach ($voters as $voter) {
            $notifications = array_merge(
                $notifications,
                $this->sendByEvent(
                    NotificationEventType::ELECTION_STARTED,
                    $voter,
                    [
                        'election_title' => $election->title,
                        'election_start_date' => $election->starts_at->format('Y-m-d H:i:s'),
                        'election_end_date' => $election->ends_at->format('Y-m-d H:i:s'),
                        'voting_url' => route('voter.dashboard'),
                    ]
                )
            );
        }

        return $notifications;
    }
}