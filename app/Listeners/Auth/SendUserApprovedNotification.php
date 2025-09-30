<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserApproved;
use App\Services\Notification\NotificationService;
use App\Enums\Notification\NotificationEventType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendUserApprovedNotification implements ShouldQueue
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
                'approval_date' => now()->format('M j, Y g:i A'),
                'login_url' => route('auth.login'),
                'approved_by' => $approvedBy?->full_name ?? 'System',
            ];

            // Send notification to the approved user
            $this->notificationService->sendByEvent(
                NotificationEventType::USER_APPROVED,
                $user,
                $data
            );

            Log::info('User approved notification sent', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'approved_by' => $approvedBy?->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send user approved notification', [
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
        Log::critical('User approved notification failed permanently', [
            'user_id' => $event->user->id ?? null,
            'error' => $exception->getMessage(),
        ]);
    }
}