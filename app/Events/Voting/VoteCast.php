<?php

namespace App\Events\Voting;

use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteAuthorization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoteCast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public VoteRecord $voteRecord,
        public VoteAuthorization $authorization
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