<?php

namespace App\Jobs\Candidate;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCandidateNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $userId,
        private string $eventType,
        private array $data
    ) {}

    public function handle(NotificationService $notifications): void
    {
        $user = User::find($this->userId);
        
        if ($user) {
            $notifications->send($user, $this->eventType, $this->data);
        }
    }
}