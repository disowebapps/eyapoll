<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Observer;
use Illuminate\Support\Facades\Hash;

class EnhancedObserverSeeder extends Seeder
{
    public function run(): void
    {
        Observer::truncate();

        $observers = [
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'John',
                'last_name' => 'Observer',
                'email' => 'john.observer@example.com',
                'phone_number' => '+1234567890',
                'password' => Hash::make('password123'),
                'type' => 'organization',
                'organization_name' => 'Democracy Watch International',
                'organization_address' => '123 Democracy St, Capital City',
                'certification_number' => 'DWI-2024-001',
                'status' => 'approved',
                'observer_privileges' => ['view_audit_logs', 'export_audit_logs', 'view_election_results'],
                'approved_at' => now(),
                'approved_by' => 1,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Jane',
                'last_name' => 'Monitor',
                'email' => 'jane.monitor@example.com',
                'phone_number' => '+1234567891',
                'password' => Hash::make('password123'),
                'type' => 'independent',
                'certification_number' => 'IND-2024-002',
                'status' => 'pending',
                'observer_privileges' => ['view_election_results'],
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Mike',
                'last_name' => 'Watcher',
                'email' => 'mike.watcher@example.com',
                'phone_number' => '+1234567892',
                'password' => Hash::make('password123'),
                'type' => 'organization',
                'organization_name' => 'Transparency Coalition',
                'organization_address' => '456 Transparency Ave, Metro City',
                'certification_number' => 'TC-2024-003',
                'status' => 'approved',
                'observer_privileges' => ['view_audit_logs', 'view_election_results', 'monitor_voting_process'],
                'approved_at' => now(),
                'approved_by' => 1,
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'Sarah',
                'last_name' => 'Auditor',
                'email' => 'sarah.auditor@example.com',
                'phone_number' => '+1234567893',
                'password' => Hash::make('password123'),
                'type' => 'independent',
                'certification_number' => 'IND-2024-004',
                'status' => 'suspended',
                'observer_privileges' => ['view_election_results'],
                'suspended_at' => now(),
                'suspended_by' => 1,
                'suspension_reason' => 'Violation of observer code of conduct',
            ],
            [
                'uuid' => \Illuminate\Support\Str::uuid(),
                'first_name' => 'David',
                'last_name' => 'Inspector',
                'email' => 'david.inspector@example.com',
                'phone_number' => '+1234567894',
                'password' => Hash::make('password123'),
                'type' => 'organization',
                'organization_name' => 'Election Integrity Foundation',
                'organization_address' => '789 Integrity Blvd, Justice City',
                'certification_number' => 'EIF-2024-005',
                'status' => 'revoked',
                'observer_privileges' => [],
                'revoked_at' => now(),
                'revoked_by' => 1,
                'revocation_reason' => 'Breach of confidentiality agreement',
            ],
        ];

        foreach ($observers as $observer) {
            Observer::create($observer);
        }
    }
}