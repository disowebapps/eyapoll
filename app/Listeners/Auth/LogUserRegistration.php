<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogUserRegistration implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        try {
            $user = $event->user;
            
            // Log the user registration action
            $this->auditLogService->log(
                'user_registered',
                $user,
                get_class($user),
                $user->id,
                null, // No old values for new registration
                [
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'status' => $user->status->value,
                    'has_phone' => !empty($user->phone_number),
                    'registration_timestamp' => $user->created_at->toISOString(),
                ]
            );

            Log::info('User registration logged successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role->value,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log user registration', [
                'user_id' => $event->user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Don't re-throw as audit logging failure shouldn't break registration
            // But we should alert administrators
            if (app()->environment('production')) {
                // In production, this could trigger an alert to administrators
                Log::critical('Audit logging failure during user registration', [
                    'user_id' => $event->user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRegistered $event, \Throwable $exception): void
    {
        Log::error('User registration audit log job failed permanently', [
            'user_id' => $event->user->id,
            'error' => $exception->getMessage(),
        ]);

        // In production, this should trigger an alert
        if (app()->environment('production')) {
            Log::critical('Critical: Audit logging system failure', [
                'event' => 'user_registered',
                'user_id' => $event->user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}