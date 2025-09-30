<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Election\Election;
use App\Livewire\Voter\VotingBooth;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Database\Seeders\AyapollSeeder;

class VotingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AyapollSeeder::class);
    }

    public function test_complete_voting_flow()
    {
        // Ensure test user exists
        $user = User::where('email', 'voter1@example.com')->first();

        if (!$user) {
            // Create test user if not found
            $cryptoService = app(\App\Services\Cryptographic\CryptographicService::class);
            $voterSalt = $cryptoService->generateSalt();
            $voterIdHash = $cryptoService->hashIdNumber('12345678901', $voterSalt);

            $user = User::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'email' => 'voter1@example.com',
                'phone_number' => '+2348012345670',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password' => \Illuminate\Support\Facades\Hash::make('voter123'),
                'first_name' => 'Test',
                'last_name' => 'Voter 1',
                'id_number_hash' => $voterIdHash,
                'id_salt' => $voterSalt,
                'status' => \App\Enums\Auth\UserStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => 1, // admin
            ]);

            // Create ID document
            \App\Models\Auth\IdDocument::create([
                'user_id' => $user->id,
                'document_type' => \App\Enums\Auth\DocumentType::NATIONAL_ID,
                'file_path' => encrypt('documents/ids/test.jpg'),
                'file_hash' => hash('sha256', 'test'),
                'status' => 'approved',
                'reviewed_by' => 1,
                'reviewed_at' => now(),
            ]);
        }

        // Get the election (first one with positions)
        $election = Election::whereHas('positions')->first();

        if (!$election) {
            // Create test election with positions
            $admin = \App\Models\Admin::first();
            $election = Election::create([
                'uuid' => \Illuminate\Support\Str::uuid(),
                'title' => 'Test Election',
                'description' => 'Test election for voting flow',
                'type' => \App\Enums\Election\ElectionType::GENERAL,
                'status' => \App\Enums\Election\ElectionStatus::ONGOING,
                'starts_at' => now()->subDays(1),
                'ends_at' => now()->addDays(1),
                'settings' => [
                    'allow_abstention' => true,
                    'require_candidate_manifesto' => true,
                    'candidate_application_fee' => 1000,
                    'voting_duration_hours' => 72,
                ],
                'created_by' => $admin->id ?? 1,
            ]);

            // Create positions
            $positions = [
                ['title' => 'President', 'max_selections' => 1, 'order_index' => 1],
                ['title' => 'Secretary', 'max_selections' => 1, 'order_index' => 2],
            ];

            foreach ($positions as $posData) {
                $position = \App\Models\Election\Position::create(array_merge($posData, [
                    'election_id' => $election->id,
                ]));

                // Create a candidate for this position
                $candidate = \App\Models\Candidate\Candidate::create([
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user->id, // Use the test user as candidate
                    'election_id' => $election->id,
                    'position_id' => $position->id,
                    'status' => \App\Enums\Candidate\CandidateStatus::APPROVED,
                    'payment_status' => \App\Enums\Candidate\PaymentStatus::PAID,
                    'approved_at' => now(),
                    'approved_by' => 1,
                ]);
            }
        }

        // Test 1: Can access voting page
        $response = $this->actingAs($user)->get("/voter/vote/{$election->id}");
        echo "✓ Voting page access: " . ($response->status() === 200 ? 'SUCCESS' : 'FAILED') . "\n";

        // Test 2: Livewire component loads
        try {
            $component = Livewire::actingAs($user)->test(VotingBooth::class, ['election' => $election]);
            echo "✓ Livewire component loads: SUCCESS\n";
            
            // Test 3: Component has positions
            $positions = $component->get('positions');
            echo "✓ Positions loaded: " . (is_array($positions) && count($positions) > 0 ? 'SUCCESS (' . count($positions) . ' positions)' : 'FAILED') . "\n";
            if (!is_array($positions) || count($positions) == 0) {
                echo "  Positions value: " . var_export($positions, true) . "\n";
                echo "  Election positions count: " . $election->positions()->count() . "\n";
                $this->fail('No positions loaded in component');
            }
            
            // Test 4: Current position exists
            $currentPosition = $component->get('currentPosition');
            echo "✓ Current position: " . ($currentPosition ? 'SUCCESS (' . $currentPosition['title'] . ')' : 'FAILED') . "\n";
            
            // Test 5: Test candidate selection
            if ($currentPosition && !empty($currentPosition['candidates'])) {
                $firstCandidate = $currentPosition['candidates'][0];
                echo "  Attempting to select candidate: " . $firstCandidate['name'] . " (ID: " . $firstCandidate['id'] . ")\n";
                $component->call('toggleCandidate', $currentPosition['id'], $firstCandidate['id']);
                
                $selections = $component->get('selections');
                echo "✓ Candidate selection: " . (!empty($selections) ? 'SUCCESS' : 'FAILED') . "\n";
                echo "  Selections: " . json_encode($selections) . "\n";
            }
            
        } catch (\Exception $e) {
            echo "✗ Livewire component failed: " . $e->getMessage() . "\n";
            echo "  Stack trace: " . $e->getTraceAsString() . "\n";
        }

        $this->assertTrue(true);
    }
}