<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification\NotificationTemplate;
use App\Enums\Notification\NotificationChannel;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // User Registration & Approval
            [
                'event_type' => 'user_registered',
                'channel' => NotificationChannel::EMAIL,
                'subject' => 'Welcome to {{ platform_name }} - Account Pending Approval',
                'body_template' => 'Dear {{ user_name }},

Thank you for registering with {{ platform_name }}!

Your account has been created and is currently under review by our administrators. You will receive an email notification once your account is approved and you can start participating in elections.

Account Details:
- Email: {{ user_email }}
- Registration Date: {{ registration_date }}

If you have any questions, please contact our support team.

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'user_approved',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Account Approved!',
                'body_template' => 'Dear {{ user_name }},

Congratulations! Your account has been approved and you are now ready to participate in elections on {{ platform_name }}.

You can now:
- Log in to your account
- View available elections
- Cast your votes securely
- Verify your votes with unique receipt hashes

Login URL: {{ login_url }}
Dashboard: {{ dashboard_url }}

Your participation helps build a stronger democracy. Thank you for joining {{ platform_name }}!

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'user_approved',
                'channel' => NotificationChannel::IN_APP,
                'subject' => 'Account Approved - Welcome to {{ platform_name }}!',
                'body_template' => 'Your account has been approved! You can now participate in elections and cast your votes securely.',
                'is_active' => true,
            ],

            // Vote Casting & Confirmation
            [
                'event_type' => 'vote_cast',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Vote Confirmation',
                'body_template' => 'Dear {{ user_name }},

Your vote has been successfully cast in the "{{ election_title }}" election!

Vote Details:
- Election: {{ election_title }}
- Position: {{ position_title }}
- Vote Hash: {{ vote_hash }}
- Cast At: {{ cast_at }}
- Election Ends: {{ election_ends_at }}

Receipt Verification:
Your unique receipt hash for verification: {{ receipt_hash }}

You can verify your vote at any time using this receipt hash at: {{ verification_url }}

Important: Keep this receipt hash secure. It proves your vote was included in the election results without revealing how you voted.

Thank you for participating in this democratic process!

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'vote_cast',
                'channel' => NotificationChannel::IN_APP,
                'subject' => 'Vote Cast Successfully',
                'body_template' => 'Your vote in "{{ election_title }}" has been recorded. Receipt hash: {{ receipt_hash }}',
                'is_active' => true,
            ],

            // Election Events
            [
                'event_type' => 'election_started',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Election Now Open: {{ election_title }}',
                'body_template' => 'Dear {{ user_name }},

The "{{ election_title }}" election is now open for voting!

Election Details:
- Type: {{ election_type }}
- Voting Period: {{ starts_at }} to {{ ends_at }}
- Time Remaining: {{ time_remaining }}
- Positions Available: {{ positions_count }}

You can cast your vote securely at: {{ voting_url }}

Important Security Features:
- Your vote is anonymous (identity separated from ballot)
- Each vote gets a unique cryptographic receipt hash
- You can verify your vote was counted without revealing how you voted
- All votes are cryptographically chained for integrity

Please participate in this important democratic process!

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'election_started',
                'channel' => NotificationChannel::IN_APP,
                'subject' => 'Election Started: {{ election_title }}',
                'body_template' => 'The "{{ election_title }}" election is now open for voting. Cast your vote securely!',
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ending_soon',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Election Ending Soon: {{ election_title }}',
                'body_template' => 'Dear {{ user_name }},

The "{{ election_title }}" election will end soon!

Time Remaining: {{ time_remaining }}
Election Ends: {{ ends_at }}

If you haven\'t voted yet, please cast your vote before the election closes at: {{ voting_url }}

Remember: Your vote matters in building a stronger democracy.

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'election_ended',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Election Results: {{ election_title }}',
                'body_template' => 'Dear {{ user_name }},

The "{{ election_title }}" election has ended.

Final Results: {{ results_url }}
Election Summary:
- Total Votes: {{ total_votes }}
- Your Vote Status: {{ vote_status }}

If you participated in this election, you can verify your vote was included using your receipt hash.

Thank you for your participation!

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],

            // Candidate Events
            [
                'event_type' => 'candidate_approved',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Candidate Application Approved',
                'body_template' => 'Dear {{ candidate_name }},

Congratulations! Your candidate application for the "{{ election_title }}" election has been approved.

Application Details:
- Election: {{ election_title }}
- Position: {{ position_title }}
- Approved At: {{ approved_at }}

Your candidacy is now visible to voters. You can view your application status and election progress in your dashboard.

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'candidate_rejected',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Candidate Application Update',
                'body_template' => 'Dear {{ candidate_name }},

We regret to inform you that your candidate application for the "{{ election_title }}" election was not approved at this time.

Reason: {{ rejection_reason }}

You can reapply for future elections or contact us if you have questions about the decision.

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],

            // System Notifications
            [
                'event_type' => 'system_maintenance',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - System Maintenance Notice',
                'body_template' => 'Dear {{ user_name }},

{{ platform_name }} will undergo scheduled maintenance.

Maintenance Window: {{ maintenance_start }} to {{ maintenance_end }}
Expected Downtime: {{ downtime_duration }}

During this period, voting may be temporarily unavailable. We apologize for any inconvenience.

Best regards,
{{ platform_name }} Team',
                'is_active' => true,
            ],
            [
                'event_type' => 'security_alert',
                'channel' => NotificationChannel::EMAIL,
                'subject' => '{{ platform_name }} - Security Alert',
                'body_template' => 'Dear {{ user_name }},

We detected unusual activity on your account.

Details: {{ alert_details }}
Time: {{ alert_time }}

If this was not you, please contact support immediately and change your password.

For your security, we recommend:
- Using a strong, unique password
- Enabling two-factor authentication
- Regularly monitoring your account activity

Best regards,
{{ platform_name }} Security Team',
                'is_active' => true,
            ],
        ];

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

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'event_type' => $template['event_type'],
                    'channel' => $template['channel'],
                ],
                array_merge($template, [
                    'created_by' => $adminUser->id,
                ])
            );
        }

        $this->command->info('Notification templates seeded successfully!');
    }
}
