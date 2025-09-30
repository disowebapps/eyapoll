<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Voting\VoteToken;
use App\Enums\Auth\UserStatus;
use App\Enums\Auth\UserRole;
use Illuminate\Support\Str;

class VerifiedUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['first_name' => 'Alice', 'last_name' => 'Johnson'],
            ['first_name' => 'Bob', 'last_name' => 'Smith'],
            ['first_name' => 'Carol', 'last_name' => 'Davis'],
            ['first_name' => 'David', 'last_name' => 'Wilson'],
            ['first_name' => 'Emma', 'last_name' => 'Brown'],
            ['first_name' => 'Frank', 'last_name' => 'Miller'],
            ['first_name' => 'Grace', 'last_name' => 'Taylor'],
            ['first_name' => 'Henry', 'last_name' => 'Anderson'],
            ['first_name' => 'Ivy', 'last_name' => 'Thomas'],
            ['first_name' => 'Jack', 'last_name' => 'Jackson'],
        ];

        foreach ($users as $index => $userData) {
            $voterNumber = $index + 6; // Start from voter6
            $user = User::create([
                'uuid' => Str::uuid(),
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'email' => "voter{$voterNumber}@ayapoll.com",
                'phone_number' => '+234' . rand(7000000000, 9999999999),
                'password' => bcrypt('password123'),
                'id_number_hash' => hash('sha256', "voter{$voterNumber}@ayapoll.com" . time()),
                'id_salt' => Str::random(32),
                'role' => UserRole::VOTER,
                'status' => UserStatus::APPROVED,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'approved_at' => now(),
                'approved_by' => 1,
                'verification_data' => [
                    'email_verified' => true,
                    'phone_verified' => true,
                    'kyc_completed' => true,
                    'kyc_approved' => true
                ]
            ]);

            // Create vote token for accreditation
            VoteToken::create([
                'user_id' => $user->id,
                'election_id' => 3, // Assuming election ID 3 exists
                'token_hash' => hash('sha256', $user->id . '3' . time() . Str::random(16)),
                'is_used' => false
            ]);
        }
    }
}