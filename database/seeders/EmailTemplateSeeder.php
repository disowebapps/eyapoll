<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
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
                'html_template' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to {{ platform_name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Welcome to {{ platform_name }}!</h1>
        <p>Dear {{ user_name }},</p>
        <p>Thank you for registering with {{ platform_name }}!</p>
        <p>Your account has been created and is currently under review by our administrators. You will receive an email notification once your account is approved and you can start participating in elections.</p>

        <div style="background: #f3f4f6; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3>Account Details:</h3>
            <ul>
                <li><strong>Email:</strong> {{ user_email }}</li>
                <li><strong>Registration Date:</strong> {{ registration_date }}</li>
            </ul>
        </div>

        <p>If you have any questions, please contact our support team.</p>

        <p>Best regards,<br>{{ platform_name }} Team</p>
    </div>
</body>
</html>',
                'variables' => ['user_name', 'user_email', 'registration_date', 'platform_name'],
                'is_active' => true,
            ],
            [
                'event_type' => 'user_approved',
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
                'html_template' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Approved - {{ platform_name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #059669;">Account Approved!</h1>
        <p>Dear {{ user_name }},</p>
        <p>Congratulations! Your account has been approved and you are now ready to participate in elections on {{ platform_name }}.</p>

        <div style="background: #ecfdf5; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #059669;">
            <h3>You can now:</h3>
            <ul>
                <li>Log in to your account</li>
                <li>View available elections</li>
                <li>Cast your votes securely</li>
                <li>Verify your votes with unique receipt hashes</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ login_url }}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Login to Your Account</a>
        </div>

        <p>Your participation helps build a stronger democracy. Thank you for joining {{ platform_name }}!</p>

        <p>Best regards,<br>{{ platform_name }} Team</p>
    </div>
</body>
</html>',
                'variables' => ['user_name', 'login_url', 'dashboard_url', 'platform_name'],
                'is_active' => true,
            ],

            // Vote Casting & Confirmation
            [
                'event_type' => 'vote_cast',
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
                'html_template' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vote Confirmation - {{ platform_name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #059669;">Vote Cast Successfully!</h1>
        <p>Dear {{ user_name }},</p>
        <p>Your vote has been successfully cast in the <strong>"{{ election_title }}"</strong> election!</p>

        <div style="background: #f0f9ff; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #2563eb;">
            <h3>Vote Details:</h3>
            <ul>
                <li><strong>Election:</strong> {{ election_title }}</li>
                <li><strong>Position:</strong> {{ position_title }}</li>
                <li><strong>Vote Hash:</strong> <code>{{ vote_hash }}</code></li>
                <li><strong>Cast At:</strong> {{ cast_at }}</li>
                <li><strong>Election Ends:</strong> {{ election_ends_at }}</li>
            </ul>
        </div>

        <div style="background: #fef3c7; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #d97706;">
            <h3>Receipt Verification:</h3>
            <p>Your unique receipt hash for verification: <strong>{{ receipt_hash }}</strong></p>
            <p>You can verify your vote at any time using this receipt hash.</p>
            <div style="text-align: center; margin: 15px 0;">
                <a href="{{ verification_url }}" style="background: #d97706; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Verify Your Vote</a>
            </div>
        </div>

        <div style="background: #fee2e2; padding: 15px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #dc2626;">
            <strong>Important:</strong> Keep this receipt hash secure. It proves your vote was included in the election results without revealing how you voted.
        </div>

        <p>Thank you for participating in this democratic process!</p>

        <p>Best regards,<br>{{ platform_name }} Team</p>
    </div>
</body>
</html>',
                'variables' => ['user_name', 'election_title', 'position_title', 'vote_hash', 'cast_at', 'election_ends_at', 'receipt_hash', 'verification_url', 'platform_name'],
                'is_active' => true,
            ],

            // Election Events
            [
                'event_type' => 'election_started',
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
                'html_template' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Election Now Open - {{ platform_name }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #2563eb;">Election Now Open!</h1>
        <p>Dear {{ user_name }},</p>
        <p>The <strong>"{{ election_title }}"</strong> election is now open for voting!</p>

        <div style="background: #f0f9ff; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3>Election Details:</h3>
            <ul>
                <li><strong>Type:</strong> {{ election_type }}</li>
                <li><strong>Voting Period:</strong> {{ starts_at }} to {{ ends_at }}</li>
                <li><strong>Time Remaining:</strong> {{ time_remaining }}</li>
                <li><strong>Positions Available:</strong> {{ positions_count }}</li>
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ voting_url }}" style="background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Cast Your Vote</a>
        </div>

        <div style="background: #ecfdf5; padding: 15px; margin: 20px 0; border-radius: 5px;">
            <h3>Security Features:</h3>
            <ul>
                <li>Your vote is anonymous (identity separated from ballot)</li>
                <li>Each vote gets a unique cryptographic receipt hash</li>
                <li>You can verify your vote was counted without revealing how you voted</li>
                <li>All votes are cryptographically chained for integrity</li>
            </ul>
        </div>

        <p>Please participate in this important democratic process!</p>

        <p>Best regards,<br>{{ platform_name }} Team</p>
    </div>
</body>
</html>',
                'variables' => ['user_name', 'election_title', 'election_type', 'starts_at', 'ends_at', 'time_remaining', 'positions_count', 'voting_url', 'platform_name'],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['event_type' => $template['event_type']],
                array_merge($template, ['created_by' => $adminUser->id])
            );
        }

        $this->command->info('Email templates seeded successfully!');
    }
}
