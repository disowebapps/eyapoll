<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteToken;
use App\Models\Voting\VoteTally;
use App\Services\Cryptographic\CryptographicService;
use App\Enums\Election\ElectionStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AddVotesToCompletedElections extends Seeder
{
    public function run(): void
    {
        $cryptoService = app(CryptographicService::class);
        
        $completedElections = Election::where('status', ElectionStatus::ENDED)
            ->with(['positions.candidates' => function($q) {
                $q->where('status', 'approved');
            }])
            ->get();

        foreach ($completedElections as $election) {
            $this->command->info("Adding votes to: {$election->title}");
            
            // Create 35 voters
            $voters = [];
            for ($i = 1; $i <= 35; $i++) {
                $salt = $cryptoService->generateSalt();
                $uniqueId = time() + $i + $election->id * 10000;
                
                $voter = User::create([
                    'uuid' => Str::uuid(),
                    'email' => "voter{$i}.e{$election->id}.{$uniqueId}@test.com",
                    'phone_number' => '+234' . str_pad($uniqueId % 9999999999, 10, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password' => Hash::make('password'),
                    'first_name' => "Voter",
                    'last_name' => "Number {$i}",
                    'id_number_hash' => $cryptoService->hashIdNumber($uniqueId . '000000', $salt),
                    'id_salt' => $salt,
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => 1,
                ]);
                
                $voters[] = $voter;
                
                // Create vote token
                VoteToken::create([
                    'user_id' => $voter->id,
                    'election_id' => $election->id,
                    'token_hash' => hash('sha256', Str::random(32)),
                    'is_used' => true,
                    'used_at' => now()->subHours(rand(1, 48)),
                ]);
            }

            $voteIndex = 0;
            
            // Create votes for each voter and position
            foreach ($voters as $voter) {
                foreach ($election->positions as $position) {
                    $candidates = $position->candidates;
                    if ($candidates->isEmpty()) continue;
                    
                    $selectedCandidate = $candidates->random();
                    
                    VoteRecord::create([
                        'vote_hash' => hash('sha256', Str::random(32)),
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'ballot_data' => ['candidate_ids' => [$selectedCandidate->id]],
                        'receipt_hash' => hash('sha256', Str::random(32)),
                        'chain_hash' => hash('sha256', 'vote' . $voteIndex),
                        'integrity_signature' => hash('sha256', 'sig' . $voteIndex),
                        'cast_at' => now()->subHours(rand(1, 48)),
                        'ip_hash' => hash('sha256', '192.168.1.' . rand(1, 255)),
                    ]);
                    
                    $voteIndex++;
                }
            }

            // Create vote tallies
            foreach ($election->positions as $position) {
                $candidates = $position->candidates;
                if ($candidates->isEmpty()) continue;
                
                $voteCounts = [];
                $totalVotes = 35;
                $remaining = $totalVotes;
                
                // Distribute votes among candidates
                foreach ($candidates as $index => $candidate) {
                    if ($index === $candidates->count() - 1) {
                        $voteCount = $remaining;
                    } else {
                        $voteCount = rand(1, min(15, $remaining - ($candidates->count() - $index - 1)));
                        $remaining -= $voteCount;
                    }
                    
                    VoteTally::create([
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'candidate_id' => $candidate->id,
                        'vote_count' => $voteCount,
                        'last_updated' => now(),
                        'tally_hash' => hash('sha256', json_encode([
                            'election_id' => $election->id,
                            'position_id' => $position->id,
                            'candidate_id' => $candidate->id,
                            'vote_count' => $voteCount,
                        ])),
                    ]);
                }
            }
            
            $totalVotes = $election->positions->count() * 35;
            $this->command->info("  Created {$totalVotes} votes and tallies");
        }
    }
}