<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Candidate\Candidate;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Candidate\CandidateService;
use App\Enums\Election\ElectionStatus;
use App\Enums\Candidate\CandidateStatus;
use App\Enums\Candidate\PaymentStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PopulateExistingElectionsSeeder extends Seeder
{
    public function run(): void
    {
        $cryptoService = app(CryptographicService::class);
        $candidateService = app(CandidateService::class);
        $admin = Admin::first();

        if (!$admin) {
            $this->command->error('No admin found. Please seed admin data first.');
            return;
        }

        // Find elections needing candidates
        $elections = Election::with(['positions', 'candidates'])
            ->whereIn('status', [ElectionStatus::SCHEDULED, ElectionStatus::ENDED])
            ->get()
            ->filter(function ($election) {
                if ($election->positions->isEmpty()) return false;
                
                foreach ($election->positions as $position) {
                    $approvedCount = $position->candidates()
                        ->where('status', CandidateStatus::APPROVED)
                        ->count();
                    if ($approvedCount < 2) return true;
                }
                return false;
            });

        if ($elections->isEmpty()) {
            $this->command->info('All elections already have adequate candidates.');
            return;
        }

        $this->command->info("Found {$elections->count()} elections needing candidates:");
        
        $candidateData = [
            ['Alice', 'Johnson', 'Committed to transparency and community development'],
            ['Bob', 'Williams', 'Experienced leader focused on sustainable growth'],
            ['Carol', 'Brown', 'Advocate for inclusive policies and social justice'],
            ['David', 'Davis', 'Business-minded with a passion for economic development'],
            ['Eva', 'Miller', 'Educator dedicated to improving public services'],
            ['Frank', 'Wilson', 'Community organizer with grassroots experience'],
            ['Grace', 'Moore', 'Healthcare professional committed to public welfare'],
            ['Henry', 'Taylor', 'Environmental advocate for green initiatives'],
            ['Iris', 'Anderson', 'Youth leader bringing fresh perspectives'],
            ['Jack', 'Thomas', 'Veteran with strong leadership credentials'],
            ['Kate', 'Jackson', 'Social worker focused on community empowerment'],
            ['Leo', 'White', 'Technology expert promoting digital innovation'],
            ['Maya', 'Harris', 'Legal professional ensuring accountability'],
            ['Noah', 'Martin', 'Economist with strategic planning expertise'],
            ['Olivia', 'Thompson', 'Artist advocating for cultural development'],
        ];

        $totalCreated = 0;

        foreach ($elections as $election) {
            $this->command->info("Processing: {$election->title}");
            $electionCandidates = 0;

            foreach ($election->positions as $position) {
                $currentCount = $position->candidates()
                    ->where('status', CandidateStatus::APPROVED)
                    ->count();
                
                $needed = max(0, 3 - $currentCount);
                
                for ($i = 0; $i < $needed; $i++) {
                    $dataIndex = ($totalCreated + $i) % count($candidateData);
                    $data = $candidateData[$dataIndex];
                    $uniqueId = time() + $totalCreated + $i;

                    // Create user
                    $salt = $cryptoService->generateSalt();
                    $user = User::create([
                        'uuid' => Str::uuid(),
                        'email' => strtolower($data[0] . '.' . $data[1] . '.' . $uniqueId . '@candidate.test'),
                        'phone_number' => '+234' . str_pad($uniqueId % 9999999999, 10, '0', STR_PAD_LEFT),
                        'email_verified_at' => now(),
                        'phone_verified_at' => now(),
                        'password' => Hash::make('password'),
                        'first_name' => $data[0],
                        'last_name' => $data[1],
                        'id_number_hash' => $cryptoService->hashIdNumber($uniqueId . '000000000', $salt),
                        'id_salt' => $salt,
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => $admin->id,
                    ]);

                    // Create candidate
                    $candidate = Candidate::create([
                        'user_id' => $user->id,
                        'election_id' => $election->id,
                        'position_id' => $position->id,
                        'manifesto' => $data[2] . " As a candidate for {$position->title}, I pledge to serve with integrity and dedication to our shared values.",
                        'status' => CandidateStatus::PENDING,
                        'payment_status' => PaymentStatus::PAID,
                        'application_fee' => 0.00,
                    ]);

                    // Approve candidate
                    $candidateService->approveCandidate($candidate, $admin, 'Test data - auto approved');
                    
                    $electionCandidates++;
                    $totalCreated++;
                }
            }

            $this->command->info("  Created {$electionCandidates} candidates");
        }

        $this->command->info("Successfully created {$totalCreated} candidates across {$elections->count()} elections!");
    }
}