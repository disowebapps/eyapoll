<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Models\User;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Enums\Election\ElectionPhase;

class VoterRegisterTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create test election
        $election = Election::create([
            'title' => 'Student Council Election 2024',
            'description' => 'Annual student council election for academic year 2024-2025',
            'type' => ElectionType::GENERAL,
            'status' => ElectionStatus::SCHEDULED,
            'phase' => ElectionPhase::VOTER_REGISTRATION,
            'voter_register_starts' => now()->subDays(10),
            'voter_register_ends' => now()->subDays(1), // Ended yesterday
            'starts_at' => now()->addDays(5),
            'ends_at' => now()->addDays(5)->addHours(8),
            'created_by' => 1,
        ]);

        // Create positions
        $positions = [
            ['title' => 'President', 'description' => 'Student Council President', 'order_index' => 1],
            ['title' => 'Vice President', 'description' => 'Student Council Vice President', 'order_index' => 2],
            ['title' => 'Secretary', 'description' => 'Student Council Secretary', 'order_index' => 3],
        ];

        foreach ($positions as $positionData) {
            Position::create(array_merge($positionData, ['election_id' => $election->id]));
        }

        // Create 33 approved users (voters)
        $voters = [];
        $timestamp = now()->timestamp;
        for ($i = 1; $i <= 33; $i++) {
            $voters[] = User::create([
                'first_name' => 'Voter',
                'last_name' => sprintf('%02d', $i),
                'email' => "voter{$i}_{$timestamp}@test.com",
                'password' => bcrypt('password'),
                'status' => 'approved',
                'email_verified_at' => now(),
                'id_number_hash' => hash('sha256', 'TEST' . str_pad($i, 6, '0', STR_PAD_LEFT) . $timestamp),
                'id_salt' => \Illuminate\Support\Str::random(32),
            ]);
        }

        // Create some candidates
        $candidateUsers = [];
        for ($i = 1; $i <= 6; $i++) {
            $candidateUsers[] = User::create([
                'first_name' => 'Candidate',
                'last_name' => sprintf('%02d', $i),
                'email' => "candidate{$i}_{$timestamp}@test.com",
                'password' => bcrypt('password'),
                'status' => 'approved',
                'email_verified_at' => now(),
                'id_number_hash' => hash('sha256', 'CAND' . str_pad($i, 6, '0', STR_PAD_LEFT) . $timestamp),
                'id_salt' => \Illuminate\Support\Str::random(32),
            ]);
        }

        // Create candidates for positions
        $positionIds = $election->positions->pluck('id')->toArray();
        foreach ($candidateUsers as $index => $user) {
            Candidate::create([
                'user_id' => $user->id,
                'election_id' => $election->id,
                'position_id' => $positionIds[$index % count($positionIds)],
                'status' => 'approved',
            ]);
        }

        $this->command->info("Created test election with 33 voters and 6 candidates");
        $this->command->info("Election ID: {$election->id}");
        $this->command->info("Voter registration deadline has passed - ready for manual publication");
    }
}