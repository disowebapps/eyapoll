<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Election\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class CandidateApplicationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_button_is_greyed_out_before_deadline()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create([
            'candidate_register_starts' => Carbon::now()->addDays(1),
            'candidate_register_ends' => Carbon::now()->addDays(7),
            'status' => 'upcoming'
        ]);

        $this->actingAs($user)
            ->get(route('voter.dashboard'))
            ->assertSee('Apply as Candidate')
            ->assertSee('bg-gray-400'); // Greyed out button
    }

    public function test_application_button_is_active_during_deadline()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create([
            'candidate_register_starts' => Carbon::now()->subDays(1),
            'candidate_register_ends' => Carbon::now()->addDays(7),
            'status' => 'upcoming'
        ]);

        $this->actingAs($user)
            ->get(route('voter.dashboard'))
            ->assertSee('Apply as Candidate')
            ->assertSee('bg-green-600'); // Active button
    }

    public function test_application_route_is_blocked_before_deadline()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create([
            'candidate_register_starts' => Carbon::now()->addDays(1),
            'candidate_register_ends' => Carbon::now()->addDays(7),
            'status' => 'upcoming'
        ]);

        $this->actingAs($user)
            ->get(route('candidate.apply', $election))
            ->assertRedirect()
            ->assertSessionHas('error');
    }

    public function test_application_route_is_blocked_after_deadline()
    {
        $user = User::factory()->create(['status' => 'approved']);
        $election = Election::factory()->create([
            'candidate_register_starts' => Carbon::now()->subDays(7),
            'candidate_register_ends' => Carbon::now()->subDays(1),
            'status' => 'upcoming'
        ]);

        $this->actingAs($user)
            ->get(route('candidate.apply', $election))
            ->assertRedirect()
            ->assertSessionHas('error');
    }
}