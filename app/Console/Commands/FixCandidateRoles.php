<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Candidate\Candidate;
use App\Enums\Auth\UserRole;

class FixCandidateRoles extends Command
{
    protected $signature = 'candidates:fix-roles';
    protected $description = 'Fix user roles for approved candidates';

    public function handle()
    {
        $candidates = Candidate::with('user')
            ->where('status', 'approved')
            ->whereHas('user', function($query) {
                $query->where('role', '!=', UserRole::CANDIDATE);
            })
            ->get();

        $fixed = 0;
        foreach ($candidates as $candidate) {
            if ($candidate->user) {
                $candidate->user->update(['role' => UserRole::CANDIDATE]);
                $fixed++;
                $this->info("Fixed role for: {$candidate->user->email}");
            }
        }

        $this->info("Fixed {$fixed} candidate roles.");
        return 0;
    }
}