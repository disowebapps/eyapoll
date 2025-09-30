<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for the AYApoll notification system.
    | It defines channels, templates, and delivery settings for various
    | notification types throughout the platform.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Channel Settings
    |--------------------------------------------------------------------------
    */
    'default_channel' => env('AYAPOLL_DEFAULT_NOTIFICATION_CHANNEL', 'email'),
    'fallback_channel' => env('AYAPOLL_FALLBACK_NOTIFICATION_CHANNEL', 'in_app'),

    /*
    |--------------------------------------------------------------------------
    | Channel Configuration
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'email' => [
            'enabled' => env('AYAPOLL_EMAIL_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@ayapoll.org'),
            'from_name' => env('MAIL_FROM_NAME', 'AYApoll'),
            'retry_attempts' => env('AYAPOLL_EMAIL_RETRY_ATTEMPTS', 3),
            'retry_delay_minutes' => env('AYAPOLL_EMAIL_RETRY_DELAY', 5),
        ],
        'sms' => [
            'enabled' => env('AYAPOLL_SMS_ENABLED', false),
            'provider' => env('AYAPOLL_SMS_PROVIDER', 'twilio'),
            'from' => env('AYAPOLL_SMS_FROM', 'AYApoll'),
            'retry_attempts' => env('AYAPOLL_SMS_RETRY_ATTEMPTS', 2),
            'retry_delay_minutes' => env('AYAPOLL_SMS_RETRY_DELAY', 2),
        ],
        'in_app' => [
            'enabled' => env('AYAPOLL_IN_APP_ENABLED', true),
            'retention_days' => env('AYAPOLL_IN_APP_RETENTION_DAYS', 30),
            'mark_read_on_view' => env('AYAPOLL_MARK_READ_ON_VIEW', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Event-to-Channel Mapping
    |--------------------------------------------------------------------------
    */
    'event_channels' => [
        'user_registered' => ['email'],
        'user_approved' => ['email', 'in_app'],
        'user_rejected' => ['email', 'in_app'],
        'email_verification' => ['email'],
        'phone_verification' => ['sms'],
        'login_verification' => ['email', 'sms'],
        'candidate_application_submitted' => ['email', 'in_app'],
        'candidate_approved' => ['email', 'in_app'],
        'candidate_rejected' => ['email', 'in_app'],
        'election_created' => ['email', 'in_app'],
        'election_started' => ['email', 'sms', 'in_app'],
        'election_ending_soon' => ['email', 'sms', 'in_app'],
        'election_ended' => ['email', 'in_app'],
        'vote_cast' => ['in_app'],
        'vote_receipt' => ['email'],
        'system_maintenance' => ['email', 'in_app'],
        'security_alert' => ['email', 'sms', 'in_app'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Configuration
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'user_registered' => [
            'subject' => 'Welcome to {{ platform_name }}',
            'email_template' => 'notifications.email.user-registered',
            'variables' => ['user_name', 'platform_name', 'verification_url'],
        ],
        'user_approved' => [
            'subject' => 'Your account has been approved',
            'email_template' => 'notifications.email.user-approved',
            'in_app_template' => 'notifications.in-app.user-approved',
            'variables' => ['user_name', 'platform_name', 'login_url'],
        ],
        'user_rejected' => [
            'subject' => 'Account application status',
            'email_template' => 'notifications.email.user-rejected',
            'in_app_template' => 'notifications.in-app.user-rejected',
            'variables' => ['user_name', 'platform_name', 'reason', 'support_email'],
        ],
        'email_verification' => [
            'subject' => 'Verify your email address',
            'email_template' => 'notifications.email.email-verification',
            'variables' => ['user_name', 'verification_code', 'expires_at'],
        ],
        'phone_verification' => [
            'sms_template' => 'Your {{ platform_name }} verification code is: {{ verification_code }}',
            'variables' => ['platform_name', 'verification_code'],
        ],
        'login_verification' => [
            'subject' => 'Login verification code',
            'email_template' => 'notifications.email.login-verification',
            'sms_template' => 'Your {{ platform_name }} login code is: {{ verification_code }}',
            'variables' => ['user_name', 'platform_name', 'verification_code', 'expires_at'],
        ],
        'candidate_application_submitted' => [
            'subject' => 'Candidate application received',
            'email_template' => 'notifications.email.candidate-application-submitted',
            'in_app_template' => 'notifications.in-app.candidate-application-submitted',
            'variables' => ['user_name', 'election_title', 'position_title', 'platform_name'],
        ],
        'candidate_approved' => [
            'subject' => 'Candidate application approved',
            'email_template' => 'notifications.email.candidate-approved',
            'in_app_template' => 'notifications.in-app.candidate-approved',
            'variables' => ['user_name', 'election_title', 'position_title', 'platform_name'],
        ],
        'candidate_rejected' => [
            'subject' => 'Candidate application status',
            'email_template' => 'notifications.email.candidate-rejected',
            'in_app_template' => 'notifications.in-app.candidate-rejected',
            'variables' => ['user_name', 'election_title', 'position_title', 'reason', 'platform_name'],
        ],
        'election_created' => [
            'subject' => 'New election: {{ election_title }}',
            'email_template' => 'notifications.email.election-created',
            'in_app_template' => 'notifications.in-app.election-created',
            'variables' => ['user_name', 'election_title', 'election_type', 'starts_at', 'platform_name'],
        ],
        'election_started' => [
            'subject' => 'Voting is now open: {{ election_title }}',
            'email_template' => 'notifications.email.election-started',
            'sms_template' => 'Voting is now open for {{ election_title }}. Vote at {{ voting_url }}',
            'in_app_template' => 'notifications.in-app.election-started',
            'variables' => ['user_name', 'election_title', 'ends_at', 'voting_url', 'platform_name'],
        ],
        'election_ending_soon' => [
            'subject' => 'Voting ends soon: {{ election_title }}',
            'email_template' => 'notifications.email.election-ending-soon',
            'sms_template' => 'Voting for {{ election_title }} ends in {{ hours_remaining }} hours. Vote now!',
            'in_app_template' => 'notifications.in-app.election-ending-soon',
            'variables' => ['user_name', 'election_title', 'ends_at', 'hours_remaining', 'voting_url', 'platform_name'],
        ],
        'election_ended' => [
            'subject' => 'Voting has ended: {{ election_title }}',
            'email_template' => 'notifications.email.election-ended',
            'in_app_template' => 'notifications.in-app.election-ended',
            'variables' => ['user_name', 'election_title', 'results_url', 'platform_name'],
        ],
        'vote_cast' => [
            'in_app_template' => 'notifications.in-app.vote-cast',
            'variables' => ['user_name', 'election_title', 'receipt_hash', 'cast_at'],
        ],
        'vote_receipt' => [
            'subject' => 'Your voting receipt: {{ election_title }}',
            'email_template' => 'notifications.email.vote-receipt',
            'variables' => ['user_name', 'election_title', 'receipt_hash', 'verification_url', 'cast_at'],
        ],
        'system_maintenance' => [
            'subject' => 'Scheduled maintenance notification',
            'email_template' => 'notifications.email.system-maintenance',
            'in_app_template' => 'notifications.in-app.system-maintenance',
            'variables' => ['user_name', 'maintenance_start', 'maintenance_end', 'platform_name'],
        ],
        'security_alert' => [
            'subject' => 'Security alert for your account',
            'email_template' => 'notifications.email.security-alert',
            'sms_template' => 'Security alert: {{ alert_type }} detected on your {{ platform_name }} account.',
            'in_app_template' => 'notifications.in-app.security-alert',
            'variables' => ['user_name', 'alert_type', 'alert_details', 'action_required', 'platform_name'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Settings
    |--------------------------------------------------------------------------
    */
    'delivery' => [
        'batch_size' => env('AYAPOLL_NOTIFICATION_BATCH_SIZE', 100),
        'rate_limit_per_minute' => env('AYAPOLL_NOTIFICATION_RATE_LIMIT', 60),
        'max_retry_attempts' => env('AYAPOLL_NOTIFICATION_MAX_RETRIES', 3),
        'retry_backoff_multiplier' => env('AYAPOLL_NOTIFICATION_BACKOFF_MULTIPLIER', 2),
        'failed_notification_retention_days' => env('AYAPOLL_FAILED_NOTIFICATION_RETENTION', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'allow_template_editing' => env('AYAPOLL_ALLOW_TEMPLATE_EDITING', true),
        'allow_channel_toggling' => env('AYAPOLL_ALLOW_CHANNEL_TOGGLING', true),
        'require_approval_for_mass_notifications' => env('AYAPOLL_REQUIRE_MASS_NOTIFICATION_APPROVAL', true),
        'log_all_notifications' => env('AYAPOLL_LOG_ALL_NOTIFICATIONS', true),
    ],
];