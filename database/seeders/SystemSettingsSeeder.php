<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = DB::table('admins')->first()?->id ?? 1;
        
        $settings = [
            // General Settings
            ['key' => 'platform_name', 'value' => 'AyaPoll', 'description' => 'Platform name', 'type' => 'string'],
            ['key' => 'platform_description', 'value' => 'Secure Electronic Voting Platform', 'description' => 'Platform description', 'type' => 'string'],
            ['key' => 'contact_email', 'value' => 'admin@ayapoll.com', 'description' => 'Contact email', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => false, 'description' => 'Maintenance mode', 'type' => 'boolean'],
            
            // Election Settings
            ['key' => 'default_election_duration', 'value' => 24, 'description' => 'Default election duration', 'type' => 'integer'],
            ['key' => 'min_candidates_per_position', 'value' => 1, 'description' => 'Minimum candidates per position', 'type' => 'integer'],
            ['key' => 'max_candidates_per_position', 'value' => 10, 'description' => 'Maximum candidates per position', 'type' => 'integer'],
            ['key' => 'allow_candidate_withdrawal', 'value' => true, 'description' => 'Allow candidate withdrawal', 'type' => 'boolean'],
            ['key' => 'require_candidate_approval', 'value' => true, 'description' => 'Require candidate approval', 'type' => 'boolean'],
            
            // Security Settings
            ['key' => 'session_timeout', 'value' => 120, 'description' => 'Session timeout', 'type' => 'integer'],
            ['key' => 'max_login_attempts', 'value' => 5, 'description' => 'Max login attempts', 'type' => 'integer'],
            ['key' => 'password_min_length', 'value' => 8, 'description' => 'Password minimum length', 'type' => 'integer'],
            ['key' => 'require_2fa', 'value' => false, 'description' => 'Require 2FA', 'type' => 'boolean'],
            ['key' => 'audit_retention_days', 'value' => 365, 'description' => 'Audit retention days', 'type' => 'integer'],
            
            // Notification Settings
            ['key' => 'email_notifications', 'value' => true, 'description' => 'Email notifications', 'type' => 'boolean'],
            ['key' => 'sms_notifications', 'value' => false, 'description' => 'SMS notifications', 'type' => 'boolean'],
            ['key' => 'notification_frequency', 'value' => 'immediate', 'description' => 'Notification frequency', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => json_encode($setting['value']),
                    'description' => $setting['description'],
                    'type' => $setting['type'],
                    'is_encrypted' => false,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}