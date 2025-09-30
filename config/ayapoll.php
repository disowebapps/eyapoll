<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AYApoll Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the AYApoll digital
    | democracy platform. These settings control various aspects of the
    | election system including security, notifications, and consensus.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Platform Settings
    |--------------------------------------------------------------------------
    */
    'platform_name' => env('AYAPOLL_PLATFORM_NAME', 'EyaPoll'),
    'organization_name' => env('AYAPOLL_ORGANIZATION_NAME', 'Democratic Organization'),
    'timezone' => env('AYAPOLL_TIMEZONE', 'Africa/Lagos'),
    'locale' => env('AYAPOLL_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue_driver' => env('AYAPOLL_QUEUE_DRIVER', 'database'),
    'queue_connection' => env('AYAPOLL_QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage_provider' => env('AYAPOLL_STORAGE_PROVIDER', 'local'),
    'document_storage_disk' => env('AYAPOLL_DOCUMENT_DISK', 'local'),
    'max_document_size' => env('AYAPOLL_MAX_DOCUMENT_SIZE', 5120), // KB

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    */
    'notification_channels' => [
        'email' => env('AYAPOLL_EMAIL_ENABLED', true),
        'sms' => env('AYAPOLL_SMS_ENABLED', false),
        'in_app' => env('AYAPOLL_IN_APP_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */
    'sms' => [
        'provider' => env('AYAPOLL_SMS_PROVIDER', 'twilio'),
        'from' => env('AYAPOLL_SMS_FROM', 'AYApoll'),
        'enabled' => env('AYAPOLL_SMS_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Consensus Configuration
    |--------------------------------------------------------------------------
    */
    'consensus' => [
        'required_approvals' => env('AYAPOLL_CONSENSUS_APPROVALS', 2),
        'admin_threshold' => env('AYAPOLL_ADMIN_THRESHOLD', 3),
        'approval_timeout_hours' => env('AYAPOLL_APPROVAL_TIMEOUT', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cryptographic Configuration
    |--------------------------------------------------------------------------
    */
    'cryptographic' => [
        'hash_algorithm' => env('AYAPOLL_HASH_ALGORITHM', 'sha256'),
        'chain_enabled' => env('AYAPOLL_CHAIN_ENABLED', true),
        'receipt_format' => env('AYAPOLL_RECEIPT_FORMAT', 'base64'),
        'id_pepper' => env('AYAPOLL_ID_PEPPER', env('APP_KEY')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    */
    'security' => [
        'recaptcha_enabled' => env('AYAPOLL_RECAPTCHA_ENABLED', true),
        'rate_limiting_enabled' => env('AYAPOLL_RATE_LIMITING_ENABLED', true),
        'login_attempts_limit' => env('AYAPOLL_LOGIN_ATTEMPTS_LIMIT', 5),
        'login_lockout_minutes' => env('AYAPOLL_LOGIN_LOCKOUT_MINUTES', 15),
        'mfa_enabled' => env('AYAPOLL_MFA_ENABLED', false), // Disabled for development
        'mfa_code_length' => env('AYAPOLL_MFA_CODE_LENGTH', 6),
        'mfa_expiry_minutes' => env('AYAPOLL_MFA_EXPIRY_MINUTES', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | KYC Configuration
    |--------------------------------------------------------------------------
    */
    'kyc' => [
        'max_resubmissions' => env('AYAPOLL_KYC_MAX_RESUBMISSIONS', 3),
        'document_types' => ['national_id', 'passport', 'drivers_license'],
        'auto_approval_threshold' => env('AYAPOLL_KYC_AUTO_APPROVAL', 85), // AI confidence score
        'manual_review_required' => env('AYAPOLL_KYC_MANUAL_REVIEW', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Election Configuration
    |--------------------------------------------------------------------------
    */
    'elections' => [
        'types' => [
            'general' => 'General Election',
            'bye' => 'Bye-Election',
            'constitutional' => 'Constitutional Poll',
            'opinion' => 'Opinion Poll',
        ],
        'default_voting_duration_hours' => env('AYAPOLL_DEFAULT_VOTING_HOURS', 24),
        'candidate_application_fee' => env('AYAPOLL_CANDIDATE_FEE', 0),
        'allow_candidate_withdrawal' => env('AYAPOLL_ALLOW_WITHDRAWAL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'log_all_actions' => env('AYAPOLL_AUDIT_ALL_ACTIONS', true),
        'chain_integrity_check' => env('AYAPOLL_CHAIN_INTEGRITY_CHECK', true),
        'export_formats' => ['csv', 'xlsx', 'json'],
        'retention_days' => env('AYAPOLL_AUDIT_RETENTION_DAYS', 2555), // 7 years
    ],

    /*
    |--------------------------------------------------------------------------
    | UI/UX Configuration
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'theme' => env('AYAPOLL_THEME', 'default'),
        'mobile_first' => env('AYAPOLL_MOBILE_FIRST', true),
        'low_bandwidth_mode' => env('AYAPOLL_LOW_BANDWIDTH', false),
        'accessibility_mode' => env('AYAPOLL_ACCESSIBILITY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    */
    'development' => [
        'bypass_recaptcha' => env('AYAPOLL_BYPASS_RECAPTCHA', true), // Enabled for development
        'bypass_mfa' => env('AYAPOLL_BYPASS_MFA', false),
        'mock_sms' => env('AYAPOLL_MOCK_SMS', true),
        'seed_test_data' => env('AYAPOLL_SEED_TEST_DATA', false),
    ],
];