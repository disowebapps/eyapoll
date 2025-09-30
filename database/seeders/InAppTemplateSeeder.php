<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InAppTemplate;

class InAppTemplateSeeder extends Seeder
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
                'title' => 'Welcome to {{ platform_name }}!',
                'message_template' => 'Your account has been created and is pending approval. You\'ll be notified once approved.',
                'icon' => 'heroicon-o-user-plus',
                'priority' => 'normal',
                'retention_days' => 7,
                'variables' => ['platform_name'],
                'is_active' => true,
            ],
            [
                'event_type' => 'user_approved',
                'title' => 'Account Approved!',
                'message_template' => 'Your account has been approved! You can now participate in elections.',
                'icon' => 'heroicon-o-check-circle',
                'action_url' => '/dashboard',
                'action_text' => 'Go to Dashboard',
                'priority' => 'high',
                'retention_days' => 30,
                'variables' => [],
                'is_active' => true,
            ],

            // Vote Casting & Confirmation
            [
                'event_type' => 'vote_cast',
                'title' => 'Vote Cast Successfully',
                'message_template' => 'Your vote in "{{ election_title }}" has been recorded. Receipt hash: {{ receipt_hash }}',
                'icon' => 'heroicon-o-check-badge',
                'action_url' => '/verify-receipt?hash={{ receipt_hash }}',
                'action_text' => 'Verify Vote',
                'priority' => 'high',
                'retention_days' => 365,
                'variables' => ['election_title', 'receipt_hash'],
                'is_active' => true,
            ],

            // Election Events
            [
                'event_type' => 'election_started',
                'title' => 'Election Started: {{ election_title }}',
                'message_template' => 'The "{{ election_title }}" election is now open for voting. {{ time_remaining }} remaining.',
                'icon' => 'heroicon-o-calendar-days',
                'action_url' => '/voter/vote/{{ election_id }}',
                'action_text' => 'Vote Now',
                'priority' => 'urgent',
                'retention_days' => 7,
                'variables' => ['election_title', 'time_remaining', 'election_id'],
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ending_soon',
                'title' => 'Election Ending Soon',
                'message_template' => '"{{ election_title }}" ends in {{ time_remaining }}. Don\'t forget to vote!',
                'icon' => 'heroicon-o-clock',
                'action_url' => '/voter/vote/{{ election_id }}',
                'action_text' => 'Vote Now',
                'priority' => 'urgent',
                'retention_days' => 1,
                'variables' => ['election_title', 'time_remaining', 'election_id'],
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ended',
                'title' => 'Election Completed',
                'message_template' => '"{{ election_title }}" has ended. View the final results.',
                'icon' => 'heroicon-o-trophy',
                'action_url' => '/results/{{ election_id }}',
                'action_text' => 'View Results',
                'priority' => 'normal',
                'retention_days' => 90,
                'variables' => ['election_title', 'election_id'],
                'is_active' => true,
            ],

            // Candidate Events
            [
                'event_type' => 'candidate_approved',
                'title' => 'Candidacy Approved!',
                'message_template' => 'Congratulations! Your candidacy for "{{ election_title }}" has been approved.',
                'icon' => 'heroicon-o-star',
                'action_url' => '/candidate/dashboard',
                'action_text' => 'View Status',
                'priority' => 'high',
                'retention_days' => 30,
                'variables' => ['election_title'],
                'is_active' => true,
            ],
            [
                'event_type' => 'candidate_rejected',
                'title' => 'Candidacy Update',
                'message_template' => 'Your candidacy application was not approved. Reason: {{ rejection_reason }}',
                'icon' => 'heroicon-o-x-circle',
                'priority' => 'normal',
                'retention_days' => 30,
                'variables' => ['rejection_reason'],
                'is_active' => true,
            ],

            // System Notifications
            [
                'event_type' => 'system_maintenance',
                'title' => 'System Maintenance',
                'message_template' => 'Scheduled maintenance from {{ maintenance_start }} to {{ maintenance_end }}. Service may be unavailable.',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'priority' => 'normal',
                'retention_days' => 1,
                'variables' => ['maintenance_start', 'maintenance_end'],
                'is_active' => true,
            ],
            [
                'event_type' => 'security_alert',
                'title' => 'Security Alert',
                'message_template' => 'Unusual activity detected on your account. Please review your security settings.',
                'icon' => 'heroicon-o-shield-exclamation',
                'action_url' => '/profile/security',
                'action_text' => 'Review Security',
                'priority' => 'urgent',
                'retention_days' => 7,
                'variables' => [],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            InAppTemplate::updateOrCreate(
                ['event_type' => $template['event_type']],
                array_merge($template, ['created_by' => $adminUser->id])
            );
        }

        $this->command->info('In-app notification templates seeded successfully!');
    }
}
