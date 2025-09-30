<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;
use App\Services\Election\ElectionPhaseManager;
use App\Enums\Election\ElectionPhase;
use Illuminate\Http\Request;

class VoterRegisterController extends Controller
{
    public function publish(Election $election, ElectionPhaseManager $phaseManager)
    {
        // Verify deadline has passed
        if (!$election->voter_register_ends || $election->voter_register_ends > now()) {
            return back()->with('error', 'Voter registration deadline has not elapsed yet.');
        }
        
        $isRepublish = $election->voter_register_published !== null;

        // Clear existing tokens if republishing to include new users
        if ($isRepublish) {
            $election->voteTokens()->delete();
        }

        try {
            // Clear existing tokens if republishing to include all current voters
            if ($isRepublish) {
                $election->voteTokens()->delete();
            }
            
            // Generate vote tokens for all eligible voters
            $tokensGenerated = $election->generateVoteTokens();
            
            // Update voter register as published
            $election->update(['voter_register_published' => now()]);
            
            // Dispatch event to pause voter registration
            \App\Events\VoterRegisterPublished::dispatch($election);
            
            $message = $isRepublish ? 
                'Voter register republished with new users included. ' : 
                'Voter register published successfully. ';
            
            return back()->with('success', $message . $tokensGenerated . ' eligible voters registered.');
        } catch (\Exception $e) {
            \Log::error('Voter register publication failed', [
                'election_id' => $election->id,
                'error' => $e->getMessage(),
                'current_phase' => $election->phase?->value
            ]);
            
            return back()->with('error', 'Failed to publish voter register: ' . $e->getMessage());
        }
    }

    public function extend(Request $request, Election $election)
    {
        // Verify register is not yet published
        if ($election->voter_register_published) {
            return back()->with('error', 'Cannot extend - voter register already published.');
        }

        $request->validate([
            'extension_date' => 'required|date|after:now',
        ]);

        $election->update(['voter_register_ends' => $request->extension_date]);

        return back()->with('success', 'Voter registration extended to ' . \Carbon\Carbon::parse($request->extension_date)->format('M d, Y H:i'));
    }

    public function restart(Request $request, Election $election, ElectionPhaseManager $phaseManager)
    {
        // Allow restart if registration has ended OR if register is published
        if (!$election->voter_register_ends || $election->voter_register_ends > now()) {
            if (!$election->voter_register_published) {
                return back()->with('error', 'Voter registration is still active. Cannot restart.');
            }
        }

        $request->validate([
            'restart_date' => 'required|date|after:now',
        ]);

        // Reset registration period but keep published register available
        $election->update([
            'voter_register_locked' => false,
            'voter_register_ends' => $request->restart_date
        ]);

        return back()->with('success', 'Voter registration restarted until ' . \Carbon\Carbon::parse($request->restart_date)->format('M d, Y H:i'));
    }
}