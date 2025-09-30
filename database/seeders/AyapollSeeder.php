<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Observer;
use App\Models\Auth\IdDocument;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Notification\NotificationTemplate;
use App\Services\Cryptographic\CryptographicService;
use App\Enums\Auth\UserStatus;
use App\Enums\Auth\DocumentType;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Enums\Notification\NotificationChannel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AyapollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cryptoService = app(CryptographicService::class);

        // Create initial admin user
        $admin = Admin::create([
            'uuid' => Str::uuid(),
            'email' => 'admin@ayapoll.com',
            'phone_number' => '+2348012345678',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'first_name' => 'Eleco',
            'last_name' => 'Chair',
            'status' => 'approved',
            'is_super_admin' => true,
            'approved_at' => now(),
        ]);

        // Create second admin for consensus testing
        $admin2 = Admin::create([
            'uuid' => Str::uuid(),
            'email' => 'admin2@ayapoll.com',
            'phone_number' => '+2348012345679',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('admin123'),
            'first_name' => 'Eleco',
            'last_name' => 'Sec',
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        // Create observer user
        $observer = Observer::create([
            'uuid' => Str::uuid(),
            'email' => 'observer@ayapoll.com',
            'phone_number' => '+2348012345680',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => Hash::make('observer123'),
            'first_name' => 'Election',
            'last_name' => 'Observer',
            'type' => 'independent',
            'status' => UserStatus::APPROVED,
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        // Create test voters
        for ($i = 1; $i <= 5; $i++) {
            $voterSalt = $cryptoService->generateSalt();
            $voterIdHash = $cryptoService->hashIdNumber('1234567890' . $i, $voterSalt);

            $voter = User::create([
                'uuid' => Str::uuid(),
                'email' => "voter{$i}@ayapoll.com",
                'phone_number' => "+23480123456{$i}0",
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('voter123'),
                'first_name' => "Test",
                'last_name' => "Voter {$i}",
                'id_number_hash' => $voterIdHash,
                'id_salt' => $voterSalt,
                'status' => UserStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);

            // Create approved ID document for each voter
            IdDocument::create([
                'user_id' => $voter->id,
                'document_type' => DocumentType::NATIONAL_ID,
                'file_path' => encrypt("documents/ids/{$voter->uuid}/national_id_test.jpg"),
                'file_hash' => hash('sha256', "test_document_{$voter->id}"),
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        }

        // Create test candidates
        for ($i = 1; $i <= 3; $i++) {
            $candidateSalt = $cryptoService->generateSalt();
            $candidateIdHash = $cryptoService->hashIdNumber('1234567891' . $i, $candidateSalt);

            $candidate = User::create([
                'uuid' => Str::uuid(),
                'email' => "candidate{$i}@example.com",
                'phone_number' => "+23480123457{$i}0",
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => Hash::make('candidate123'),
                'first_name' => "Test",
                'last_name' => "Candidate {$i}",
                'id_number_hash' => $candidateIdHash,
                'id_salt' => $candidateSalt,
                'status' => UserStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);

            // Create approved ID document for each candidate
            IdDocument::create([
                'user_id' => $candidate->id,
                'document_type' => DocumentType::NATIONAL_ID,
                'file_path' => encrypt("documents/ids/{$candidate->uuid}/national_id_test.jpg"),
                'file_hash' => hash('sha256', "test_document_{$candidate->id}"),
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);
        }

        // Create pending users for testing approval process
        for ($i = 1; $i <= 3; $i++) {
            $pendingSalt = $cryptoService->generateSalt();
            $pendingIdHash = $cryptoService->hashIdNumber('1234567892' . $i, $pendingSalt);

            $pendingUser = User::create([
                'uuid' => Str::uuid(),
                'email' => "pending{$i}@example.com",
                'email_verified_at' => now(),
                'password' => Hash::make('pending123'),
                'first_name' => "Pending",
                'last_name' => "User {$i}",
                'id_number_hash' => $pendingIdHash,
                'id_salt' => $pendingSalt,
                'status' => UserStatus::PENDING,
            ]);

            // Create pending ID document
            IdDocument::create([
                'user_id' => $pendingUser->id,
                'document_type' => DocumentType::NATIONAL_ID,
                'file_path' => encrypt("documents/ids/{$pendingUser->uuid}/national_id_pending.jpg"),
                'file_hash' => hash('sha256', "pending_document_{$pendingUser->id}"),
                'status' => 'pending',
            ]);
        }

        // Create multiple elections
        $elections = [
            [
                'title' => 'EYA National Executive Election 2025',
                'description' => 'General election for EYA National Executive positions.',
                'type' => ElectionType::GENERAL,
                'status' => ElectionStatus::UPCOMING,
                'starts_at' => now()->addDays(7),
                'ends_at' => now()->addDays(10),
                'fee' => 10000,
            ],
            [
                'title' => 'EYA Constitutional Amendment Referendum 2025',
                'description' => 'Referendum on proposed amendments to the EYA Constitution.',
                'type' => ElectionType::CONSTITUTIONAL,
                'status' => ElectionStatus::UPCOMING,
                'starts_at' => now()->addDays(14),
                'ends_at' => now()->addDays(17),
                'fee' => 0,
            ],
            [
                'title' => 'EYA Anambra Chapter Election 2025',
                'description' => 'Election for Anambra Chapter Executive positions.',
                'type' => ElectionType::BYE,
                'status' => ElectionStatus::ONGOING,
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(1),
                'fee' => 5000,
            ],
            [
                'title' => 'EYA Youth Development Policy Survey',
                'description' => 'Opinion poll on youth development policies and priorities.',
                'type' => ElectionType::OPINION,
                'status' => ElectionStatus::COMPLETED,
                'starts_at' => now()->subDays(30),
                'ends_at' => now()->subDays(27),
                'fee' => 0,
            ],
        ];

        foreach ($elections as $electionData) {
            $election = Election::create([
                'uuid' => Str::uuid(),
                'title' => $electionData['title'],
                'description' => $electionData['description'],
                'type' => $electionData['type'],
                'status' => $electionData['status'],
                'starts_at' => $electionData['starts_at'],
                'ends_at' => $electionData['ends_at'],
                'settings' => [
                    'allow_abstention' => true,
                    'require_candidate_manifesto' => $electionData['type'] !== ElectionType::OPINION,
                    'candidate_application_fee' => $electionData['fee'],
                    'voting_duration_hours' => 72,
                ],
                'created_by' => $admin->id,
            ]);

            // Only create positions for the first election (main EYA election)
            if ($electionData['title'] === 'EYA National Executive Election 2024') {
                $this->createEYAPositions($election->id);
            }
        }



        // Create notification templates
        $this->createNotificationTemplates();

        $this->command->info('AYApoll seeder completed successfully!');
        $this->command->info('EYA Election System seeded successfully!');
        $this->command->info('Admin credentials: admin@ayapoll.com / admin123');
        $this->command->info('Admin 2 credentials: admin2@ayapoll.com / admin123');
        $this->command->info('Observer credentials: observer@ayapoll.com / observer123');
        $this->command->info('Test voter credentials: voter1@example.com / voter123 (voter1-voter5)');
        $this->command->info('Test candidate credentials: candidate1@example.com / candidate123 (candidate1-candidate3)');
    }

    /**
     * Create EYA positions for the main election
     */
    private function createEYAPositions($electionId): void
    {
        $positions = [
            [
                'title' => 'President',
                'description' => 'Chief Executive Officer of the Echara Youth Assembly',
                'max_selections' => 1,
                'order_index' => 1,
            ],
            [
                'title' => 'Secretary',
                'description' => 'Secretary of the Echara Youth Assembly',
                'max_selections' => 1,
                'order_index' => 2,
            ],
            [
                'title' => 'Treasurer',
                'description' => 'Financial Secretary and Treasurer of the Assembly',
                'max_selections' => 1,
                'order_index' => 3,
            ],
            [
                'title' => 'Director of Socials',
                'description' => 'Director of Social Affairs and Events',
                'max_selections' => 1,
                'order_index' => 4,
            ],
            [
                'title' => 'Director of Sports',
                'description' => 'Director of Sports and Recreation',
                'max_selections' => 1,
                'order_index' => 5,
            ],
            [
                'title' => 'Public Relations Officer',
                'description' => 'Public Relations and Communications Officer',
                'max_selections' => 1,
                'order_index' => 6,
            ],
            [
                'title' => 'Legal Adviser',
                'description' => 'Legal Adviser and Constitutional Affairs',
                'max_selections' => 1,
                'order_index' => 7,
            ],
            [
                'title' => 'Welfare Officer',
                'description' => 'Youth Welfare and Development Officer',
                'max_selections' => 1,
                'order_index' => 8,
            ],
        ];

        foreach ($positions as $positionData) {
            Position::create(array_merge($positionData, [
                'election_id' => $electionId,
            ]));
        }
    }

    /**
     * Create default notification templates
     */
    private function createNotificationTemplates(): void
    {
        $admin = Admin::where('email', 'admin@ayapoll.com')->first();

        $templates = [
            [
                'event_type' => 'user_registered',
                'channel' => NotificationChannel::EMAIL,
                'subject' => 'Welcome to {{ platform_name }}',
                'body_template' => 'Hello {{ user_name }},\n\nWelcome to {{ platform_name }}! Your registration has been received and is pending approval.\n\nNext steps:\n1. Verify your email address\n2. Upload your ID document\n3. Wait for admin approval\n\nThank you for joining our democratic platform.',
                'variables' => ['user_name', 'platform_name', 'verification_url'],
            ],
            [
                'event_type' => 'user_approved',
                'channel' => NotificationChannel::EMAIL,
                'subject' => 'Your account has been approved',
                'body_template' => 'Hello {{ user_name }},\n\nGreat news! Your {{ platform_name }} account has been approved.\n\nYou can now:\n- Vote in active elections\n- Apply as a candidate (if eligible)\n- Access your dashboard\n\nLogin here: {{ login_url }}',
                'variables' => ['user_name', 'platform_name', 'login_url'],
            ],
            [
                'event_type' => 'email_verification',
                'channel' => NotificationChannel::EMAIL,
                'subject' => 'Verify your email address',
                'body_template' => 'Hello {{ user_name }},\n\nYour verification code is: {{ verification_code }}\n\nThis code expires at {{ expires_at }}.\n\nIf you did not request this, please ignore this email.',
                'variables' => ['user_name', 'verification_code', 'expires_at'],
            ],
            [
                'event_type' => 'login_verification',
                'channel' => NotificationChannel::EMAIL,
                'subject' => 'Login verification code',
                'body_template' => 'Hello {{ user_name }},\n\nYour login verification code is: {{ verification_code }}\n\nThis code expires in 10 minutes.\n\nIf you did not attempt to login, please contact support immediately.',
                'variables' => ['user_name', 'verification_code', 'expires_at'],
            ],
        ];

        foreach ($templates as $templateData) {
            NotificationTemplate::create(array_merge($templateData, [
                'is_active' => true,
                'created_by' => $admin->id,
            ]));
        }
    }
}