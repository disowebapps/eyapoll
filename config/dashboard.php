<?php

return [
    'thresholds' => [
        'pending_approvals' => env('DASHBOARD_PENDING_THRESHOLD', 10),
        'failed_logins' => env('DASHBOARD_FAILED_LOGIN_THRESHOLD', 50),
        'memory_limit' => env('DASHBOARD_MEMORY_LIMIT', 128),
        'queue_limit' => env('DASHBOARD_QUEUE_LIMIT', 1000),
    ],
    
    'cache' => [
        'stats_ttl' => env('DASHBOARD_STATS_CACHE', 300),
        'charts_ttl' => env('DASHBOARD_CHARTS_CACHE', 300),
        'integrity_ttl' => env('DASHBOARD_INTEGRITY_CACHE', 600),
    ],
    
    'defaults' => [
        'ballot_secrecy' => 95,
        'election_integrity' => 90,
        'notification_delivery' => 100,
        'vote_verification' => 100,
    ],
    
    'refresh_interval' => env('DASHBOARD_REFRESH_INTERVAL', 30000),
];