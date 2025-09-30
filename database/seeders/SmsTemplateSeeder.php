<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SmsTemplate;

class SmsTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get system admin user (create if doesn't exist)
        $adminUser = \App\Models\User::where('role', 'admin')->first();
        if (!$adminUser) {
            $adminUser = \App\Models\User::create([
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => 'admin@ayapoll.local',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'status' => 'approved',
                'uuid' => \Illuminate\Support\Str::uuid(),
                'id_number_hash' => hash('sha256', 'SYSTEM_ADMIN_' . time()),
                'id_salt' => \Illuminate\Support\Str::random(32),
            ]);
        }

        $templates = [
            // User Registration & Approval
            [
                'event_type' => 'user_registered',
                'message_template' => 'Welcome to {{ platform_name }}! Your account is pending approval. You\'ll receive a confirmation once approved.',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name'],
                'is_active' => true,
            ],
            [
                'event_type' => 'user_approved',
                'message_template' => '{{ platform_name }}: Account approved! You can now vote in elections. Login: {{ login_url }}',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'login_url'],
                'is_active' => true,
            ],

            // Vote Casting & Confirmation
            [
                'event_type' => 'vote_cast',
                'message_template' => '{{ platform_name }}: Vote cast in "{{ election_title }}". Receipt: {{ receipt_hash }}. Verify: {{ verification_url }}',
                'max_length' => 160,
                'estimated_cost' => 0.02,
                'variables' => ['platform_name', 'election_title', 'receipt_hash', 'verification_url'],
                'is_active' => true,
            ],

            // Election Events
            [
                'event_type' => 'election_started',
                'message_template' => '{{ platform_name }}: "{{ election_title }}" election is now open! Vote at: {{ voting_url }}. Ends: {{ ends_at }}',
                'max_length' => 160,
                'estimated_cost' => 0.02,
                'variables' => ['platform_name', 'election_title', 'voting_url', 'ends_at'],
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ending_soon',
                'message_template' => '{{ platform_name }}: "{{ election_title }}" ends soon! {{ time_remaining }} left. Vote now: {{ voting_url }}',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'election_title', 'time_remaining', 'voting_url'],
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ended',
                'message_template' => '{{ platform_name }}: "{{ election_title }}" election ended. View results: {{ results_url }}',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'election_title', 'results_url'],
                'is_active' => true,
            ],

            // Candidate Events
            [
                'event_type' => 'candidate_approved',
                'message_template' => '{{ platform_name }}: Congratulations! Your candidacy for "{{ election_title }}" has been approved.',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'election_title'],
                'is_active' => true,
            ],
            [
                'event_type' => 'candidate_rejected',
                'message_template' => '{{ platform_name }}: Your candidacy application was not approved. Reason: {{ rejection_reason }}',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'rejection_reason'],
                'is_active' => true,
            ],

            // System Notifications
            [
                'event_type' => 'system_maintenance',
                'message_template' => '{{ platform_name }}: System maintenance {{ maintenance_start }} to {{ maintenance_end }}. Service may be unavailable.',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name', 'maintenance_start', 'maintenance_end'],
                'is_active' => true,
            ],
            [
                'event_type' => 'security_alert',
                'message_template' => '{{ platform_name }}: Security alert detected. Please check your account and change password if needed.',
                'max_length' => 160,
                'estimated_cost' => 0.01,
                'variables' => ['platform_name'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                ['event_type' => $template['event_type']],
                array_merge($template, ['created_by' => $adminUser->id])
            );
        }

        $this->command->info('SMS templates seeded successfully!');
    }
}
