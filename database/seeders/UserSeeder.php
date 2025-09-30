<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'email' => 'admin@ayapoll.com',
            'password' => Hash::make('Admin1010'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'phone_number' => '+1234567890',
            'id_number_hash' => hash('sha256', '12345678901'),
            'id_salt' => 'admin_salt',
            'role' => UserRole::ADMIN,
            'status' => UserStatus::APPROVED,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'approved_at' => now(),
        ]);

        // Voter user
        User::create([
            'email' => 'voter@ayapoll.com',
            'password' => Hash::make('password'),
            'first_name' => 'John',
            'last_name' => 'Voter',
            'phone_number' => '+1234567891',
            'id_number_hash' => hash('sha256', '12345678902'),
            'id_salt' => 'voter_salt',
            'role' => UserRole::VOTER,
            'status' => UserStatus::APPROVED,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'approved_at' => now(),
        ]);

        // Candidate user
        User::create([
            'email' => 'candidate@ayapoll.com',
            'password' => Hash::make('password'),
            'first_name' => 'Jane',
            'last_name' => 'Candidate',
            'phone_number' => '+1234567892',
            'id_number_hash' => hash('sha256', '12345678903'),
            'id_salt' => 'candidate_salt',
            'role' => UserRole::CANDIDATE,
            'status' => UserStatus::APPROVED,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'approved_at' => now(),
        ]);

        // Observer user
        User::create([
            'email' => 'observer@ayapoll.com',
            'password' => Hash::make('password'),
            'first_name' => 'Bob',
            'last_name' => 'Observer',
            'phone_number' => '+1234567893',
            'id_number_hash' => hash('sha256', '12345678904'),
            'id_salt' => 'observer_salt',
            'role' => UserRole::OBSERVER,
            'status' => UserStatus::APPROVED,
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'approved_at' => now(),
        ]);
    }
}