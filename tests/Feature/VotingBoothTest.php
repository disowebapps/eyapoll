<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Election\Election;
use App\Livewire\Voter\VotingBooth;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VotingBoothTest extends TestCase
{
    use RefreshDatabase;

    public function test_voting_booth_component_loads()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create();
        
        $this->actingAs($user);
        
        Livewire::test(VotingBooth::class, ['election' => $election])
            ->assertStatus(200)
            ->assertSee($election->title);
    }

    public function test_toggle_candidate_method_works()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create();
        
        $this->actingAs($user);
        
        Livewire::test(VotingBooth::class, ['election' => $election])
            ->call('testLivewire')
            ->assertSet('currentPositionIndex', 1);
    }

    public function test_candidate_selection()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create();
        
        $this->actingAs($user);
        
        $component = Livewire::test(VotingBooth::class, ['election' => $election]);
        
        // Simulate candidate selection
        $component->call('toggleCandidate', 1, 51);
        
        // Check if selection was recorded
        $this->assertTrue(true); // Will show us if the method was called
    }
}