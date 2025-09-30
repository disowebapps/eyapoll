<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $election = Election::where('type', 'general')->first();
        $positions = Position::where('election_id', $election->id)->get();
        $users = User::where('status', 'approved')->take(8)->get();

        $candidateCounter = 1;
        $usedCombinations = [];
        
        foreach ($positions as $position) {
            $availableUsers = $users->reject(function($user) use ($usedCombinations, $election, $position) {
                return in_array("{$user->id}-{$election->id}-{$position->id}", $usedCombinations);
            });
            
            $candidatesForPosition = $availableUsers->random(min(2, $availableUsers->count()));
            
            foreach ($candidatesForPosition as $user) {
                Candidate::create([
                    'user_id' => $user->id,
                    'election_id' => $election->id,
                    'position_id' => $position->id,
                    'manifesto' => "I am committed to serving in the role of {$position->title} with dedication and transparency.",
                    'payment_status' => 'paid',
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => 1,
                ]);
                $usedCombinations[] = "{$user->id}-{$election->id}-{$position->id}";
                $candidateCounter++;
            }
        }

        $this->command->info('Candidates seeded successfully!');
    }
}