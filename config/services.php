<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'geocoding_enabled' => env('GOOGLE_MAPS_GEOCODING_ENABLED', true),
        'places_enabled' => env('GOOGLE_MAPS_PLACES_ENABLED', true),
    ],

    'background_check' => [
        'default' => env('BACKGROUND_CHECK_PROVIDER', 'mock'),

        'providers' => [
            'checkr' => [
                'type' => 'checkr',
                'api_key' => env('CHECKR_API_KEY'),
                'base_url' => env('CHECKR_BASE_URL', 'https://api.checkr.com'),
                'webhook_secret' => env('CHECKR_WEBHOOK_SECRET'),
                'enabled' => env('CHECKR_ENABLED', false),
            ],

            'sterling' => [
                'type' => 'sterling',
                'api_key' => env('STERLING_API_KEY'),
                'base_url' => env('STERLING_BASE_URL', 'https://api.sterling.com'),
                'webhook_secret' => env('STERLING_WEBHOOK_SECRET'),
                'enabled' => env('STERLING_ENABLED', false),
            ],

            'mock' => [
                'type' => 'mock',
                'enabled' => env('MOCK_BACKGROUND_CHECK_ENABLED', true),
            ],
        ],
    ],

    'face_api' => [
        'models_url' => env('FACE_API_MODELS_URL', 'https://cdn.jsdelivr.net/npm/face-api.js@0.22.2'),
        'min_confidence' => env('FACE_API_MIN_CONFIDENCE', 0.6),
        'max_descriptor_distance' => env('FACE_API_MAX_DESCRIPTOR_DISTANCE', 0.6),
        'input_size' => env('FACE_API_INPUT_SIZE', 512),
        'score_threshold' => env('FACE_API_SCORE_THRESHOLD', 0.5),
    ],

    'aml' => [
        'providers' => [
            'ofac' => [
                'enabled' => env('OFAC_SCREENING_ENABLED', false),
                'api_key' => env('OFAC_API_KEY'),
                'base_url' => env('OFAC_BASE_URL'),
            ],

            'dow_jones' => [
                'enabled' => env('DOW_JONES_SCREENING_ENABLED', false),
                'api_key' => env('DOW_JONES_API_KEY'),
                'base_url' => env('DOW_JONES_BASE_URL'),
            ],
        ],

        'risk_thresholds' => [
            'high' => env('AML_HIGH_RISK_THRESHOLD', 0.8),
            'medium' => env('AML_MEDIUM_RISK_THRESHOLD', 0.5),
            'low' => env('AML_LOW_RISK_THRESHOLD', 0.2),
        ],

        'auto_flag_threshold' => env('AML_AUTO_FLAG_THRESHOLD', 0.7),
    ],

    'gdpr' => [
        'data_export_retention_days' => env('GDPR_EXPORT_RETENTION_DAYS', 30),
        'deletion_grace_period_days' => env('GDPR_DELETION_GRACE_PERIOD_DAYS', 30),
        'auto_cleanup_enabled' => env('GDPR_AUTO_CLEANUP_ENABLED', true),
        'export_formats' => ['json', 'pdf'],
    ],

];
