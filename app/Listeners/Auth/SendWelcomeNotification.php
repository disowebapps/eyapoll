<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendWelcomeNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private NotificationService $notificationService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            $user = $event->user;
            
            // Send welcome notification via email
            $this->notificationService->send(
                $user,
                'user_registered',
                [
                    'user_name' => $user->full_name,
                    'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
                    'verification_url' => route('auth.register.step2'),
                    'login_url' => route('auth.login'),
                    'support_email' => config('mail.from.address'),
                ],
                'email'
            );

            // Send in-app notification
            if (config('ayapoll.notification_channels.in_app', true)) {
                $this->notificationService->send(
                    $user,
                    'user_registered',
                    [
                        'user_name' => $user->full_name,
                        'platform_name' => config('ayapoll.platform_name', 'AYApoll'),
                        'next_step' => 'Please verify your email address to continue.',
                    ],
                    'in_app'
                );
            }

            Log::info('Welcome notification sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome notification', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Re-throw to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('Welcome notification job failed permanently', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}