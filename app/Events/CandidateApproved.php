<?php

namespace App\Events;

use App\Models\Candidate\Candidate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CandidateApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Candidate $candidate) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('user.' . $this->candidate->user_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => 'Your candidacy has been approved!',
            'role' => 'candidate'
        ];
    }
}