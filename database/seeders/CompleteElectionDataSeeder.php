<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Candidate\CandidateService;
use App\Enums\Election\ElectionType;
use App\Enums\Candidate\CandidateStatus;
use App\Enums\Candidate\PaymentStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CompleteElectionDataSeeder extends Seeder
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

        // Get elections without positions or adequate candidates
        $elections = Election::with(['positions', 'candidates'])->get();
        
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
            ['Paul', 'Garcia', 'Engineer focused on infrastructure development'],
            ['Quinn', 'Rodriguez', 'Journalist promoting media transparency'],
            ['Rachel', 'Lewis', 'Nurse advocating for healthcare reform'],
            ['Sam', 'Lee', 'Teacher committed to educational excellence'],
            ['Tina', 'Walker', 'Entrepreneur driving economic innovation'],
        ];

        $totalCreated = 0;
        $candidateIndex = 0;

        foreach ($elections as $election) {
            $this->command->info("Processing: {$election->title} (ID: {$election->id})");
            
            // Skip if election already has adequate setup
            if ($election->positions->count() > 0 && $election->candidates->count() >= 2) {
                $this->command->info("  Already has positions and candidates - skipping");
                continue;
            }

            // Create positions if none exist
            if ($election->positions->isEmpty()) {
                $positions = $this->createPositionsForElection($election);
                $this->command->info("  Created {$positions->count()} positions");
            } else {
                $positions = $election->positions;
            }

            // Create candidates for each position
            $electionCandidates = 0;
            foreach ($positions as $position) {
                $currentCount = $position->candidates()
                    ->where('status', CandidateStatus::APPROVED)
                    ->count();
                
                $needed = max(0, 3 - $currentCount);
                
                for ($i = 0; $i < $needed; $i++) {
                    $data = $candidateData[$candidateIndex % count($candidateData)];
                    $uniqueId = time() + $candidateIndex;

                    // Create user
                    $salt = $cryptoService->generateSalt();
                    $user = User::create([
                        'uuid' => Str::uuid(),
                        'email' => strtolower($data[0] . '.' . $data[1] . '.' . $uniqueId . '@candidate.test'),
                        'phone_number' => '+234' . str_pad(($uniqueId % 9999999999), 10, '0', STR_PAD_LEFT),
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
                        'manifesto' => $data[2] . " As a candidate for {$position->title}, I pledge to serve with integrity and dedication.",
                        'status' => CandidateStatus::PENDING,
                        'payment_status' => PaymentStatus::PAID,
                        'application_fee' => 0.00,
                    ]);

                    // Approve candidate
                    $candidateService->approveCandidate($candidate, $admin, 'Test data - auto approved');
                    
                    $electionCandidates++;
                    $candidateIndex++;
                }
            }

            $this->command->info("  Created {$electionCandidates} candidates");
            $totalCreated += $electionCandidates;
        }

        $this->command->info("Successfully created {$totalCreated} candidates!");
    }

    private function createPositionsForElection(Election $election)
    {
        $positions = collect();

        switch ($election->type) {
            case ElectionType::GENERAL:
                $positionData = [
                    ['President', 'Chief Executive Officer', 1],
                    ['Vice President', 'Deputy Chief Executive', 1],
                    ['Secretary', 'Administrative Officer', 1],
                    ['Treasurer', 'Financial Officer', 1],
                ];
                break;

            case ElectionType::CONSTITUTIONAL:
                $positionData = [
                    ['Yes/No Vote', 'Constitutional Amendment Decision', 1],
                ];
                break;

            case ElectionType::OPINION:
                $positionData = [
                    ['Policy Option A', 'Support for Policy Direction A', 1],
                    ['Policy Option B', 'Support for Policy Direction B', 1],
                ];
                break;

            case ElectionType::BYE:
                $positionData = [
                    ['Vacant Position', 'Fill vacant leadership role', 1],
                ];
                break;

            default:
                $positionData = [
                    ['Chairperson', 'Leadership Position', 1],
                    ['Secretary', 'Administrative Position', 1],
                ];
        }

        foreach ($positionData as $index => $data) {
            $position = Position::create([
                'election_id' => $election->id,
                'title' => $data[0],
                'description' => $data[1],
                'max_selections' => $data[2],
                'order_index' => $index + 1,
                'is_active' => true,
            ]);
            $positions->push($position);
        }

        return $positions;
    }
}