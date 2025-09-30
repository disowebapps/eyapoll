<?php

namespace App\Events\Token;

use App\Models\Voting\VoteToken;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TokenIssued
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public VoteToken $token,
        public User|Admin $admin
    ) {}
}