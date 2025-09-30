<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserApproved;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendUserApprovalNotification implements ShouldQueue
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
    public function handle(UserApproved $event): void
    {
        try {
            $user = $event->user;
            $approvedBy = $event->approvedBy;

            // Prepare notification data
            $data = [
                'user_name' => $user->full_name,
                'user_email' => $user->email,
                'approved_at' => now()->format('M j, Y g:i A'),
                'approved_by' => $approvedBy?->full_name ?? 'System',
                'login_url' => route('auth.login'),
                'dashboard_url' => route('voter.dashboard'),
            ];

            // Send approval notification
            $this->notificationService->sendByEvent(
                NotificationEventType::USER_APPROVED,
                $user,
                $data
            );

            Log::info('User approval notification sent', [
                'user_id' => $user->id,
                'approved_by' => $approvedBy?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send user approval notification', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserApproved $event, \Throwable $exception): void
    {
        Log::critical('User approval notification failed permanently', [
            'user_id' => $event->user->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}