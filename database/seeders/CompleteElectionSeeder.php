<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Models\Voting\VoteToken;
use App\Models\Voting\Vote;
use App\Models\Voting\VoteTally;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Services\Cryptographic\CryptographicService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CompleteElectionSeeder extends Seeder
{
    public function run(): void
    {
        $cryptoService = app(CryptographicService::class);
        $admin = Admin::first();

        // Create election
        $election = Election::create([
            'uuid' => Str::uuid(),
            'title' => 'Complete Test Election 2025',
            'description' => 'Full election with candidates, voters, and results',
            'type' => ElectionType::GENERAL,
            'status' => ElectionStatus::ENDED,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subHours(1),
            'settings' => ['allow_abstention' => true],
            'created_by' => $admin->id,
        ]);

        // Create position
        $position = Position::create([
            'election_id' => $election->id,
            'title' => 'President',
            'description' => 'Chief Executive Officer',
            'max_selections' => 1,
            'order_index' => 1,
        ]);

        // Create 10 candidates using proper service layer
        $candidates = [];
        $candidateService = app(\App\Services\Candidate\CandidateService::class);
        
        for ($i = 1; $i <= 10; $i++) {
            $salt = $cryptoService->generateSalt();
            $user = User::create([
                'uuid' => Str::uuid(),
                'email' => "testcandidate{$i}@example.com",
                'phone_number' => "+234901234{$i}000",
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('password'),
                'first_name' => "Candidate",
                'last_name' => "Number {$i}",
                'id_number_hash' => $cryptoService->hashIdNumber("12345678{$i}0", $salt),
                'id_salt' => $salt,
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);
            
            $candidate = Candidate::create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'position_id' => $position->id,
                'manifesto' => "I will serve with integrity - Candidate {$i}",
                'status' => 'pending',
                'payment_status' => 'paid',
            ]);
            
            // Use service to properly approve and assign role
            $candidateService->approveCandidate($candidate, $admin, 'Test data approval');
            $candidates[] = $candidate->fresh();
        }

        // Create 50 voters (role defaults to voter)
        $voters = [];
        for ($i = 1; $i <= 50; $i++) {
            $salt = $cryptoService->generateSalt();
            $voters[] = User::create([
                'uuid' => Str::uuid(),
                'email' => "testvoter{$i}@example.com",
                'phone_number' => "+234902345{$i}000",
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('password'),
                'first_name' => "Voter",
                'last_name' => "Number {$i}",
                'id_number_hash' => $cryptoService->hashIdNumber("98765432{$i}0", $salt),
                'id_salt' => $salt,
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);
        }

        // Generate vote tokens for all voters
        foreach ($voters as $voter) {
            VoteToken::create([
                'user_id' => $voter->id,
                'election_id' => $election->id,
                'token_hash' => hash('sha256', Str::random(32)),
                'is_used' => true,
                'used_at' => now()->subHours(rand(1, 48)),
            ]);
        }

        // Simulate realistic voting results
        $voteDistribution = [
            1 => 12, 2 => 8, 3 => 7, 4 => 6, 5 => 5,
            6 => 4, 7 => 3, 8 => 2, 9 => 2, 10 => 1,
        ];

        // Create votes and tallies
        $voteIndex = 0;
        foreach ($voteDistribution as $candidateIndex => $voteCount) {
            $candidate = $candidates[$candidateIndex - 1];
            
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

            for ($v = 0; $v < $voteCount; $v++) {
                $voteHash = hash('sha256', Str::random(32));
                $receiptHash = hash('sha256', Str::random(32));
                
                Vote::create([
                    'vote_hash' => $voteHash,
                    'election_id' => $election->id,
                    'position_id' => $position->id,
                    'ballot_data' => ['candidate_ids' => [$candidate->id]],
                    'receipt_hash' => $receiptHash,
                    'chain_hash' => hash('sha256', $voteHash . $voteIndex),
                    'integrity_signature' => hash('sha256', 'signature' . $voteIndex),
                    'cast_at' => now()->subHours(rand(1, 48)),
                    'ip_hash' => hash('sha256', '192.168.1.' . rand(1, 255)),
                ]);
                
                $voteIndex++;
            }
        }

        $this->command->info('Complete election seeded successfully!');
        $this->command->info("Election: {$election->title} (ID: {$election->id})");
        $this->command->info('Candidates: 10, Voters: 50, Votes: 50');
        $this->command->info('Winner: Candidate Number 1 (12 votes, 24%)');
    }
}