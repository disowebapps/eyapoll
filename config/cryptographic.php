<?php

return [
    'hash_algorithm' => 'sha256',
    
    'id_hashing' => [
        'algorithm' => 'sha256',
        'iterations' => 10000,
        'pepper' => env('ID_HASH_PEPPER', env('APP_KEY')),
    ],
    
    'tokens' => [
        'vote_token_length' => 64,
    ],
    
    'receipts' => [
        'include_timestamp' => true,
        'include_election_info' => true,
        'format' => 'hex',
    ],
    
    'vote_chain' => [
        'enabled' => true,
        'genesis_hash' => 'ayapoll_genesis_block',
        'include_timestamp' => true,
    ],
    
    'audit_integrity' => [
        'enabled' => true,
    ],
    
    'signatures' => [
        'enabled' => false,
        'key_size' => 2048,
        'private_key_path' => storage_path('keys/private.pem'),
        'public_key_path' => storage_path('keys/public.pem'),
    ],
];