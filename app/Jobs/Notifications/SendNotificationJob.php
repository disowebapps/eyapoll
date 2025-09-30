<?php

namespace App\Jobs\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationChannel;
use App\Enums\Notification\NotificationStatus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1 min, 5 min, 15 min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Notification $notification
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $notificationService = app(NotificationService::class);
            
            // Get template for rendering
            $template = $notificationService->getTemplate(
                $this->notification->type,
                $this->notification->channel
            );

            if (!$template) {
                throw new \Exception("No template found for {$this->notification->type} on {$this->notification->channel->value}");
            }

            // Render notification content
            $content = $notificationService->renderTemplate($template, $this->notification->data);

            // Send based on channel
            switch ($this->notification->channel) {
                case NotificationChannel::EMAIL:
                    $this->sendEmail($content);
                    break;
                    
                case NotificationChannel::SMS:
                    $this->sendSms($content);
                    break;
                    
                case NotificationChannel::IN_APP:
                    $this->sendInApp($content);
                    break;
            }

            // Mark as sent
            $notificationService->markAsSent($this->notification);
            
            Log::info('Notification sent successfully', [
                'notification_id' => $this->notification->id,
                'type' => $this->notification->type,
                'channel' => $this->notification->channel->value,
                'recipient' => $this->notification->notifiable_id,
            ]);

        } catch (\Exception $e) {
            $notificationService = app(NotificationService::class);
            $notificationService->markAsFailed($this->notification, $e->getMessage());
            
            Log::error('Notification sending failed', [
                'notification_id' => $this->notification->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Send email notification
     */
    private function sendEmail(array $content): void
    {
        $recipient = $this->notification->notifiable;
        
        if (!$recipient || !$recipient->email) {
            throw new \Exception('Invalid email recipient');
        }

        Mail::raw($content['body'], function ($message) use ($recipient, $content) {
            $message->to($recipient->email, $recipient->full_name ?? $recipient->email)
                   ->subject($content['subject'])
                   ->from(
                       config('mail.from.address'),
                       config('ayapoll.platform_name', 'AYApoll')
                   );
        });
    }

    /**
     * Send SMS notification
     */
    private function sendSms(array $content): void
    {
        $recipient = $this->notification->notifiable;
        
        if (!$recipient || !$recipient->phone_number) {
            throw new \Exception('Invalid SMS recipient');
        }

        // Check if SMS is enabled
        if (!config('ayapoll.notification_channels.sms', false)) {
            throw new \Exception('SMS notifications are disabled');
        }

        // Mock SMS sending in development
        if (config('ayapoll.development.mock_sms', true)) {
            Log::info('Mock SMS sent', [
                'to' => $recipient->phone_number,
                'message' => $content['body'],
            ]);
            return;
        }

        // Real SMS implementation would go here
        // Example with Twilio:
        /*
        $twilio = new \Twilio\Rest\Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $twilio->messages->create(
            $recipient->phone_number,
            [
                'from' => config('services.twilio.from'),
                'body' => $content['body']
            ]
        );
        */
        
        throw new \Exception('SMS provider not configured');
    }

    /**
     * Send in-app notification
     */
    private function sendInApp(array $content): void
    {
        // In-app notifications are stored in database and displayed in UI
        // No external sending required, just mark as sent
        Log::info('In-app notification ready for display', [
            'notification_id' => $this->notification->id,
            'recipient' => $this->notification->notifiable_id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $notificationService = app(NotificationService::class);
        $notificationService->markAsFailed($this->notification, $exception->getMessage());
        
        Log::error('Notification job failed permanently', [
            'notification_id' => $this->notification->id,
            'type' => $this->notification->type,
            'channel' => $this->notification->channel->value,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // In production, this could trigger an alert to administrators
        if (app()->environment('production')) {
            Log::critical('Critical: Notification system failure', [
                'notification_id' => $this->notification->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return $this->backoff;
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(30);
    }
}