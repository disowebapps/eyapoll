<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;
use Illuminate\Http\Request;

class CandidateRegisterController extends Controller
{
    public function setPeriod(Request $request, Election $election)
    {
        $request->validate([
            'start_date' => 'required|date|after:now',
            'end_date' => 'required|date|after:start_date',
        ]);

        $election->update([
            'candidate_register_starts' => $request->start_date,
            'candidate_register_ends' => $request->end_date,
        ]);

        return redirect()->route('admin.elections.show', $election->id)
            ->with('success', 'Candidate application period set successfully.');
    }

    public function extend(Request $request, Election $election)
    {
        $request->validate([
            'extension_date' => 'required|date|after:now',
        ]);

        $election->update([
            'candidate_register_ends' => $request->extension_date,
        ]);

        return back()->with('success', 'Candidate application period extended successfully.');
    }

    public function restart(Request $request, Election $election)
    {
        $request->validate([
            'restart_date' => 'required|date|after:now',
        ]);

        $election->update([
            'candidate_register_starts' => now(),
            'candidate_register_ends' => $request->restart_date,
        ]);

        return back()->with('success', 'Candidate applications restarted until ' . \Carbon\Carbon::parse($request->restart_date)->format('M d, Y H:i'));
    }

    public function publishList(Election $election)
    {
        if (!$election->candidate_register_ends || $election->candidate_register_ends > now()) {
            return back()->with('error', 'Candidate application period has not ended yet.');
        }

        if ($election->candidate_list_published) {
            return back()->with('error', 'Candidate list is already published.');
        }

        $approvedCandidates = $election->candidates()->where('status', 'approved')->count();
        
        $election->update(['candidate_list_published' => now()]);

        return back()->with('success', "Candidate list published successfully. {$approvedCandidates} approved candidates are now publicly visible.");
    }
}