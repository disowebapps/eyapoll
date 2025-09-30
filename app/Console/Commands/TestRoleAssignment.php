<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Services\Candidate\CandidateService;
use App\Enums\Auth\UserRole;

class TestRoleAssignment extends Command
{
    protected $signature = 'test:role-assignment';
    protected $description = 'Test role assignment workflow';

    public function handle()
    {
        $admin = Admin::first();
        if (!$admin) {
            $this->error('No admin found');
            return 1;
        }

        // Create test user
        $user = User::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'email' => 'test.role@example.com',
            'phone_number' => '+2349012345678',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'first_name' => 'Test',
            'last_name' => 'User',
            'id_number_hash' => hash('sha256', '1234567890'),
            'id_salt' => 'test_salt',
            'status' => 'approved',
        ]);

        $this->info("Created user: {$user->email} with role: {$user->role->value}");

        // Create election and position
        $election = Election::create([
            'uuid' => \Illuminate\Support\Str::uuid(),
            'title' => 'Test Election',
            'description' => 'Test',
            'type' => 'general',
            'status' => 'scheduled',
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(2),
            'created_by' => $admin->id,
        ]);

        $position = Position::create([
            'election_id' => $election->id,
            'title' => 'Test Position',
            'description' => 'Test',
            'max_selections' => 1,
            'order_index' => 1,
        ]);

        // Create candidate application
        $candidate = Candidate::create([
            'user_id' => $user->id,
            'election_id' => $election->id,
            'position_id' => $position->id,
            'manifesto' => 'Test manifesto',
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $this->info("Created candidate application for: {$user->email}");

        // Test approval workflow
        $candidateService = app(CandidateService::class);
        $candidateService->approveCandidate($candidate, $admin, 'Test approval');

        $user->refresh();
        $this->info("After approval, user role: {$user->role->value}");

        // Cleanup
        $candidate->delete();
        $position->delete();
        $election->delete();
        $user->delete();

        $this->info('Test completed successfully!');
        return 0;
    }
}