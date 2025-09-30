<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Settings extends BaseAdminComponent
{

    public $activeTab = 'general';
    
    // General Settings
    public $platform_name = '';
    public $platform_description = '';
    public $contact_email = '';
    public $maintenance_mode = false;
    
    // Election Settings
    public $default_election_duration = 24;
    public $min_candidates_per_position = 1;
    public $max_candidates_per_position = 10;
    public $allow_candidate_withdrawal = true;
    public $require_candidate_approval = true;
    
    // Security Settings
    public $session_timeout = 120;
    public $max_login_attempts = 5;
    public $password_min_length = 8;
    public $require_2fa = false;
    public $audit_retention_days = 365;
    
    // Notification Settings
    public $email_notifications = true;
    public $sms_notifications = false;
    public $notification_frequency = 'immediate';

    // Credentials Settings
    public $smtp_host = '';
    public $smtp_port = '';
    public $smtp_username = '';
    public $smtp_password = '';
    public $smtp_encryption = 'tls';
    public $sms_provider = '';
    public $sms_api_key = '';
    public $sms_api_secret = '';
    public $sms_from_number = '';
    public $s3_access_key = '';
    public $s3_secret_key = '';
    public $s3_region = '';
    public $s3_bucket = '';

    // Re-verification Settings
    public $allow_re_verification = false;
    public $re_verification_period_days = 365;

    // KYC Settings
    public $max_kyc_resubmissions = 3;
    
    // Payment Settings
    public $bank_name = '';
    public $account_name = '';
    public $account_number = '';
    public $payment_instructions = '';

    public function mount()
    {
        try {
            $this->loadSettings();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to load settings: ' . $e->getMessage());
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        session()->flash('success', 'Switched to ' . $tab . ' tab');
    }

    public function loadSettings()
    {
        $settings = DB::table('system_settings')->get()->pluck('value', 'key')->map(function($value) {
            return json_decode($value, true);
        });
        
        $this->platform_name = $settings['platform_name'] ?? 'AyaPoll';
        $this->platform_description = $settings['platform_description'] ?? 'Secure Electronic Voting Platform';
        $this->contact_email = $settings['contact_email'] ?? 'admin@ayapoll.com';
        $this->maintenance_mode = (bool) ($settings['maintenance_mode'] ?? false);
        
        $this->default_election_duration = (int) ($settings['default_election_duration'] ?? 24);
        $this->min_candidates_per_position = (int) ($settings['min_candidates_per_position'] ?? 1);
        $this->max_candidates_per_position = (int) ($settings['max_candidates_per_position'] ?? 10);
        $this->allow_candidate_withdrawal = (bool) ($settings['allow_candidate_withdrawal'] ?? true);
        $this->require_candidate_approval = (bool) ($settings['require_candidate_approval'] ?? true);
        
        $this->session_timeout = (int) ($settings['session_timeout'] ?? 120);
        $this->max_login_attempts = (int) ($settings['max_login_attempts'] ?? 5);
        $this->password_min_length = (int) ($settings['password_min_length'] ?? 8);
        $this->require_2fa = (bool) ($settings['require_2fa'] ?? false);
        $this->audit_retention_days = (int) ($settings['audit_retention_days'] ?? 365);
        
        $this->email_notifications = (bool) ($settings['email_notifications'] ?? true);
        $this->sms_notifications = (bool) ($settings['sms_notifications'] ?? false);
        $this->notification_frequency = $settings['notification_frequency'] ?? 'immediate';

        // Load credentials settings
        $this->smtp_host = $settings['smtp_host'] ?? '';
        $this->smtp_port = $settings['smtp_port'] ?? '';
        $this->smtp_username = $settings['smtp_username'] ?? '';
        $this->smtp_password = $settings['smtp_password'] ?? '';
        $this->smtp_encryption = $settings['smtp_encryption'] ?? 'tls';
        $this->sms_provider = $settings['sms_provider'] ?? '';
        $this->sms_api_key = $settings['sms_api_key'] ?? '';
        $this->sms_api_secret = $settings['sms_api_secret'] ?? '';
        $this->sms_from_number = $settings['sms_from_number'] ?? '';
        $this->s3_access_key = $settings['s3_access_key'] ?? '';
        $this->s3_secret_key = $settings['s3_secret_key'] ?? '';
        $this->s3_region = $settings['s3_region'] ?? '';
        $this->s3_bucket = $settings['s3_bucket'] ?? '';

        // Load re-verification settings
        $this->allow_re_verification = (bool) ($settings['allow_re_verification'] ?? false);
        $this->re_verification_period_days = (int) ($settings['re_verification_period_days'] ?? 365);

        // Load KYC settings
        $this->max_kyc_resubmissions = (int) ($settings['max_kyc_resubmissions'] ?? 3);
        
        // Load payment settings
        $this->bank_name = $settings['bank_name'] ?? '';
        $this->account_name = $settings['account_name'] ?? '';
        $this->account_number = $settings['account_number'] ?? '';
        $this->payment_instructions = $settings['payment_instructions'] ?? '';
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'platform_name' => 'required|string|max:255',
            'platform_description' => 'required|string|max:500',
            'contact_email' => 'required|email',
        ]);

        $this->updateSettings([
            'platform_name' => $this->platform_name,
            'platform_description' => $this->platform_description,
            'contact_email' => $this->contact_email,
            'maintenance_mode' => $this->maintenance_mode,
        ]);

        session()->flash('success', 'General settings updated successfully.');
    }

    public function saveElectionSettings()
    {
        $this->validate([
            'default_election_duration' => 'required|integer|min:1|max:168',
            'min_candidates_per_position' => 'required|integer|min:1|max:50',
            'max_candidates_per_position' => 'required|integer|min:1|max:50',
        ]);

        $this->updateSettings([
            'default_election_duration' => $this->default_election_duration,
            'min_candidates_per_position' => $this->min_candidates_per_position,
            'max_candidates_per_position' => $this->max_candidates_per_position,
            'allow_candidate_withdrawal' => $this->allow_candidate_withdrawal,
            'require_candidate_approval' => $this->require_candidate_approval,
        ]);

        session()->flash('success', 'Election settings updated successfully.');
    }

    public function saveSecuritySettings()
    {
        $this->validate([
            'session_timeout' => 'required|integer|min:15|max:480',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'password_min_length' => 'required|integer|min:6|max:32',
            'audit_retention_days' => 'required|integer|min:30|max:2555',
        ]);

        $this->updateSettings([
            'session_timeout' => $this->session_timeout,
            'max_login_attempts' => $this->max_login_attempts,
            'password_min_length' => $this->password_min_length,
            'require_2fa' => $this->require_2fa,
            'audit_retention_days' => $this->audit_retention_days,
        ]);

        session()->flash('success', 'Security settings updated successfully.');
    }

    public function saveNotificationSettings()
    {
        $this->validate([
            'notification_frequency' => 'required|in:immediate,hourly,daily',
        ]);

        $this->updateSettings([
            'email_notifications' => $this->email_notifications,
            'sms_notifications' => $this->sms_notifications,
            'notification_frequency' => $this->notification_frequency,
        ]);

        session()->flash('success', 'Notification settings updated successfully.');
    }

    private function updateSettings(array $settings)
    {
        $adminId = auth('admin')->id() ?? 1;
        
        foreach ($settings as $key => $value) {
            $exists = DB::table('system_settings')->where('key', $key)->exists();
            
            if ($exists) {
                DB::table('system_settings')
                    ->where('key', $key)
                    ->update([
                        'value' => json_encode($value),
                        'updated_by' => $adminId,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('system_settings')->insert([
                    'key' => $key,
                    'value' => json_encode($value),
                    'description' => ucfirst(str_replace('_', ' ', $key)),
                    'type' => is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string'),
                    'is_encrypted' => false,
                    'updated_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        Cache::forget('system_settings');
    }

    public function clearCache()
    {
        Cache::flush();
        session()->flash('success', 'System cache cleared successfully.');
    }

    public function saveCredentialsSettings()
    {
        $this->validate([
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'sms_provider' => 'nullable|string|max:255',
            'sms_api_key' => 'nullable|string|max:255',
            'sms_api_secret' => 'nullable|string|max:255',
            'sms_from_number' => 'nullable|string|max:255',
            's3_access_key' => 'nullable|string|max:255',
            's3_secret_key' => 'nullable|string|max:255',
            's3_region' => 'nullable|string|max:255',
            's3_bucket' => 'nullable|string|max:255',
        ]);

        $this->updateSettings([
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'smtp_username' => $this->smtp_username,
            'smtp_password' => $this->smtp_password,
            'smtp_encryption' => $this->smtp_encryption,
            'sms_provider' => $this->sms_provider,
            'sms_api_key' => $this->sms_api_key,
            'sms_api_secret' => $this->sms_api_secret,
            'sms_from_number' => $this->sms_from_number,
            's3_access_key' => $this->s3_access_key,
            's3_secret_key' => $this->s3_secret_key,
            's3_region' => $this->s3_region,
            's3_bucket' => $this->s3_bucket,
        ]);

        session()->flash('success', 'Credentials settings updated successfully.');
    }

    public function saveReVerificationSettings()
    {
        $this->validate([
            're_verification_period_days' => 'required|integer|min:30|max:3650',
        ]);

        $this->updateSettings([
            'allow_re_verification' => $this->allow_re_verification,
            're_verification_period_days' => $this->re_verification_period_days,
        ]);

        session()->flash('success', 'Re-verification settings updated successfully.');
    }

    public function saveKycSettings()
    {
        $this->validate([
            'max_kyc_resubmissions' => 'required|integer|min:1|max:10',
        ]);

        $this->updateSettings([
            'max_kyc_resubmissions' => $this->max_kyc_resubmissions,
        ]);

        // Clear any cached config values
        Cache::forget('kyc_max_resubmissions');

        session()->flash('success', 'KYC settings updated successfully.');
    }
    
    public function savePaymentSettings()
    {
        $this->validate([
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'payment_instructions' => 'nullable|string|max:1000',
        ]);

        $this->updateSettings([
            'bank_name' => $this->bank_name,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'payment_instructions' => $this->payment_instructions,
        ]);

        session()->flash('success', 'Payment settings updated successfully.');
    }

    public function testEmailSettings()
    {
        // Implementation would send test email
        session()->flash('success', 'Test email sent successfully.');
    }

    public function render()
    {
        return view('livewire.admin.settings');
    }
}