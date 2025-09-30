<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Services\Cryptographic\CryptographicService;
use App\Services\Candidate\CandidateService;
use App\Enums\Election\ElectionStatus;
use App\Enums\Candidate\CandidateStatus;
use App\Enums\Candidate\PaymentStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PopulateElectionCandidates extends Command
{
    protected $signature = 'election:populate-candidates {--dry-run : Show what would be done without making changes}';
    protected $description = 'Populate elections that lack candidates with test candidate data';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }

        // Find elections that need candidates
        $electionsNeedingCandidates = $this->findElectionsNeedingCandidates();
        
        if ($electionsNeedingCandidates->isEmpty()) {
            $this->info('✅ All elections have adequate candidates');
            return;
        }

        $this->info("📊 Found {$electionsNeedingCandidates->count()} elections needing candidates:");
        
        foreach ($electionsNeedingCandidates as $election) {
            $this->line("  • {$election->title} (ID: {$election->id}) - Status: {$election->status->value}");
            $this->line("    Positions: {$election->positions->count()}, Current Candidates: {$election->candidates->count()}");
        }

        if (!$isDryRun && !$this->confirm('Proceed with populating candidates?')) {
            return;
        }

        $cryptoService = app(CryptographicService::class);
        $candidateService = app(CandidateService::class);
        $admin = Admin::first();

        if (!$admin) {
            $this->error('❌ No admin found. Please seed admin data first.');
            return;
        }

        $totalCandidatesCreated = 0;

        foreach ($electionsNeedingCandidates as $election) {
            $this->info("\n🗳️  Processing: {$election->title}");
            
            if ($isDryRun) {
                $candidatesNeeded = $this->calculateCandidatesNeeded($election);
                $this->line("  Would create {$candidatesNeeded} candidates");
                continue;
            }

            $candidatesCreated = $this->populateElectionCandidates($election, $cryptoService, $candidateService, $admin);
            $totalCandidatesCreated += $candidatesCreated;
            
            $this->info("  ✅ Created {$candidatesCreated} candidates");
        }

        if (!$isDryRun) {
            $this->info("\n🎉 Successfully created {$totalCandidatesCreated} candidates across {$electionsNeedingCandidates->count()} elections");
        }
    }

    private function findElectionsNeedingCandidates()
    {
        return Election::with(['positions', 'candidates'])
            ->whereIn('status', [ElectionStatus::UPCOMING, ElectionStatus::COMPLETED])
            ->get()
            ->filter(function ($election) {
                // Skip if no positions
                if ($election->positions->isEmpty()) {
                    return false;
                }

                // Check if any position has fewer than 2 candidates
                foreach ($election->positions as $position) {
                    $approvedCandidates = $position->candidates()
                        ->where('status', CandidateStatus::APPROVED)
                        ->count();
                    
                    if ($approvedCandidates < 2) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function calculateCandidatesNeeded(Election $election): int
    {
        $needed = 0;
        foreach ($election->positions as $position) {
            $current = $position->candidates()->where('status', CandidateStatus::APPROVED)->count();
            $needed += max(0, 3 - $current); // Aim for 3 candidates per position
        }
        return $needed;
    }

    private function populateElectionCandidates(Election $election, CryptographicService $cryptoService, CandidateService $candidateService, Admin $admin): int
    {
        $candidatesCreated = 0;
        $candidateNames = [
            ['John', 'Smith'], ['Jane', 'Doe'], ['Michael', 'Johnson'], ['Sarah', 'Williams'],
            ['David', 'Brown'], ['Lisa', 'Davis'], ['Robert', 'Miller'], ['Emily', 'Wilson'],
            ['James', 'Moore'], ['Jessica', 'Taylor'], ['William', 'Anderson'], ['Ashley', 'Thomas'],
            ['Christopher', 'Jackson'], ['Amanda', 'White'], ['Daniel', 'Harris'], ['Stephanie', 'Martin'],
            ['Matthew', 'Thompson'], ['Michelle', 'Garcia'], ['Anthony', 'Martinez'], ['Kimberly', 'Robinson']
        ];

        $manifestos = [
            "I am committed to transparent governance and accountability to the people.",
            "My vision is to build a stronger, more inclusive community for all.",
            "I will work tirelessly to ensure effective representation and service delivery.",
            "Together, we can create positive change and sustainable development.",
            "I bring experience, integrity, and a passion for public service.",
            "My priority is to listen to constituents and act on their needs.",
            "I stand for progress, unity, and responsible leadership.",
            "I will champion policies that benefit all members of our community."
        ];

        foreach ($election->positions as $position) {
            $currentCandidates = $position->candidates()->where('status', CandidateStatus::APPROVED)->count();
            $candidatesNeeded = max(0, 3 - $currentCandidates);

            for ($i = 0; $i < $candidatesNeeded; $i++) {
                $nameIndex = ($candidatesCreated + $i) % count($candidateNames);
                $manifestoIndex = ($candidatesCreated + $i) % count($manifestos);
                
                $firstName = $candidateNames[$nameIndex][0];
                $lastName = $candidateNames[$nameIndex][1];
                $uniqueId = time() + $candidatesCreated + $i;

                // Create user
                $salt = $cryptoService->generateSalt();
                $user = User::create([
                    'uuid' => Str::uuid(),
                    'email' => strtolower($firstName . '.' . $lastName . '.' . $uniqueId . '@testcandidate.com'),
                    'phone_number' => '+234' . str_pad($uniqueId, 10, '0', STR_PAD_LEFT),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
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
                    'manifesto' => $manifestos[$manifestoIndex] . " I am running for {$position->title} to serve our community with dedication.",
                    'status' => CandidateStatus::PENDING,
                    'payment_status' => PaymentStatus::PAID,
                    'application_fee' => 0.00,
                ]);

                // Approve candidate
                $candidateService->approveCandidate($candidate, $admin, 'Auto-approved test candidate');
                
                $candidatesCreated++;
            }
        }

        return $candidatesCreated;
    }
}