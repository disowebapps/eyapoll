<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Election\Election;
use App\Livewire\Voter\VotingBooth;
use Livewire\Livewire;

class LivewireDebugTest extends TestCase
{
    public function test_livewire_with_real_user()
    {
        // Get the exact user you're logged in as
        $user = User::firstOrCreate(
            ['email' => 'voter1@example.com'],
            [
                'first_name' => 'Voter',
                'last_name' => 'One',
                'password' => bcrypt('password'), // or Hash::make if you prefer
            ]
        );
        $election = Election::find(3);
        
        echo "=== LIVEWIRE DEBUG TEST ===\n";
        echo "User: {$user->first_name} {$user->last_name} (ID: {$user->id})\n";
        echo "Election: {$election->title} (ID: {$election->id})\n\n";
        
        // Test the exact same flow as browser
        $component = Livewire::actingAs($user)->test(VotingBooth::class, ['election' => $election]);
        
        echo "1. Component mounted successfully\n";
        echo "2. Positions count: " . count($component->get('positions')) . "\n";
        echo "3. Current position index: " . $component->get('currentPositionIndex') . "\n";
        echo "4. Current position: " . ($component->get('currentPosition')['title'] ?? 'NULL') . "\n";
        echo "5. Initial selections: " . json_encode($component->get('selections')) . "\n\n";
        
        // Test the exact toggleCandidate call
        $currentPosition = $component->get('currentPosition');
        $firstCandidate = $currentPosition['candidates'][0];
        
        echo "Testing toggleCandidate({$currentPosition['id']}, {$firstCandidate['id']})\n";
        echo "Candidate: {$firstCandidate['name']}\n\n";
        
        // This is the exact same call that should happen in browser
        $component->call('toggleCandidate', $currentPosition['id'], $firstCandidate['id']);
        
        echo "AFTER TOGGLE:\n";
        echo "Selections: " . json_encode($component->get('selections')) . "\n";
        echo "Current position index: " . $component->get('currentPositionIndex') . "\n";
        
        // Test if we can call testLivewire method
        echo "\nTesting testLivewire method:\n";
        $oldIndex = $component->get('currentPositionIndex');
        $component->call('testLivewire');
        $newIndex = $component->get('currentPositionIndex');
        echo "Position index changed from {$oldIndex} to {$newIndex}\n";
        
        $this->assertTrue(true);
    }
}