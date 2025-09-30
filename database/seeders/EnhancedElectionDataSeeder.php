<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Models\Voting\VoteRecord;
use App\Models\Voting\VoteToken;
use App\Models\Voting\VoteTally;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Candidate\CandidateService;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Enums\Candidate\CandidateStatus;
use App\Enums\Candidate\PaymentStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EnhancedElectionDataSeeder extends Seeder
{
    public function run(): void
    {
        $cryptoService = app(CryptographicService::class);
        $candidateService = app(CandidateService::class);
        $admin = Admin::first();

        $elections = Election::with(['positions', 'candidates'])->get();
        
        $candidateNames = [
            'Alice Johnson', 'Bob Williams', 'Carol Brown', 'David Davis', 'Eva Miller',
            'Frank Wilson', 'Grace Moore', 'Henry Taylor', 'Iris Anderson', 'Jack Thomas',
            'Kate Jackson', 'Leo White', 'Maya Harris', 'Noah Martin', 'Olivia Thompson',
            'Paul Garcia', 'Quinn Rodriguez', 'Rachel Lewis', 'Sam Lee', 'Tina Walker',
            'Uma Patel', 'Victor Chen', 'Wendy Kim', 'Xavier Lopez', 'Yara Ahmed',
            'Zoe Clark', 'Adam Scott', 'Beth Green', 'Carl Young', 'Diana Hall'
        ];

        $manifestos = [
            'Committed to transparency and accountability',
            'Focused on sustainable community development',
            'Advocate for inclusive policies and social justice',
            'Business-minded with economic development focus',
            'Dedicated to improving public services',
            'Community organizer with grassroots experience',
            'Healthcare professional committed to public welfare',
            'Environmental advocate for green initiatives'
        ];

        $candidateIndex = 0;

        foreach ($elections as $election) {
            $this->command->info("Processing: {$election->title}");
            
            // Clear existing positions and candidates
            $election->candidates()->delete();
            $election->positions()->delete();

            // Create 5 positions
            $positions = $this->createPositions($election);
            
            // Create 5 candidates per position
            foreach ($positions as $position) {
                for ($i = 0; $i < 5; $i++) {
                    $name = explode(' ', $candidateNames[$candidateIndex % count($candidateNames)]);
                    $uniqueId = time() + $candidateIndex;

                    $salt = $cryptoService->generateSalt();
                    $user = User::create([
                        'uuid' => Str::uuid(),
                        'email' => strtolower($name[0] . '.' . $name[1] . '.' . $uniqueId . '@test.com'),
                        'phone_number' => '+234' . str_pad($uniqueId % 9999999999, 10, '0', STR_PAD_LEFT),
                        'email_verified_at' => now(),
                        'phone_verified_at' => now(),
                        'password' => Hash::make('password'),
                        'first_name' => $name[0],
                        'last_name' => $name[1],
                        'id_number_hash' => $cryptoService->hashIdNumber($uniqueId . '000000000', $salt),
                        'id_salt' => $salt,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $admin->id,
                    ]);

                    $candidate = Candidate::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'manifesto' => $manifestos[$candidateIndex % count($manifestos)] . " for {$position->title}.",
                        'status' => CandidateStatus::PENDING,
                        'payment_status' => PaymentStatus::PAID,
                        'application_fee' => 0.00,
                    ]);

                    $candidateService->approveCandidate($candidate, $admin, 'Auto-approved');
                    $candidateIndex++;
                }
            }

            // Add votes for completed elections
            if ($election->status === ElectionStatus::ENDED) {
                $this->addVotesToElection($election, $cryptoService);
            }

            $this->command->info("  Created 5 positions, 25 candidates" . 
                ($election->status === ElectionStatus::ENDED ? ", 30+ votes" : ""));
        }
    }

    private function createPositions(Election $election)
    {
        $positionTitles = [
            'President', 'Vice President', 'Secretary', 'Treasurer', 'Public Relations Officer'
        ];

        $positions = [];
        foreach ($positionTitles as $index => $title) {
            $positions[] = Position::create([
                'election_id' => $election->id,
                'title' => $title,
                'description' => "Leadership position: {$title}",
                'max_selections' => 1,
                'order_index' => $index + 1,
                'is_active' => true,
            ]);
        }
        return $positions;
    }

    private function addVotesToElection(Election $election, CryptographicService $cryptoService)
    {
        // Create 35 voters
        $voters = [];
        $timestamp = time();
        for ($i = 1; $i <= 35; $i++) {
            $salt = $cryptoService->generateSalt();
            $uniqueId = $timestamp + $i + $election->id * 1000;
            $voters[] = User::create([
                'uuid' => Str::uuid(),
                'email' => "voter{$i}.election{$election->id}.{$uniqueId}@test.com",
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
        }

        // Create vote tokens and votes
        $voteIndex = 0;
        foreach ($voters as $voter) {
            VoteToken::create([
                'user_id' => $voter->id,
                'election_id' => $election->id,
                'token_hash' => hash('sha256', Str::random(32)),
                'is_used' => true,
                'used_at' => now()->subHours(rand(1, 48)),
            ]);

            // Vote for each position
            foreach ($election->positions as $position) {
                $candidates = $position->candidates()->where('status', 'approved')->get();
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
            $candidates = $position->candidates()->where('status', 'approved')->get();
            $totalVotes = 35;
            $votesDistributed = 0;

            foreach ($candidates as $index => $candidate) {
                $voteCount = $index === $candidates->count() - 1 ? 
                    $totalVotes - $votesDistributed : 
                    rand(3, 12);
                
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
                
                $votesDistributed += $voteCount;
            }
        }
    }
}