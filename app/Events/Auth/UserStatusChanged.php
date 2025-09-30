<?php

namespace App\Events\Auth;

use App\Models\User;
use App\Models\Admin;
use App\Enums\Auth\UserStatus;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public UserStatus $oldStatus,
        public UserStatus $newStatus,
        public ?Admin $changedBy = null,
        public array $metadata = []
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}