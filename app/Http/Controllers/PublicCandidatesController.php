<?php

namespace App\Http\Controllers;

use App\Models\Election\Election;

class PublicCandidatesController extends Controller
{
    public function index(Election $election)
    {
        if (!$election->candidate_list_published) {
            abort(404, 'Candidate list not yet published');
        }

        $candidates = $election->candidates()
            ->where('status', 'approved')
            ->with(['position', 'user'])
            ->get()
            ->groupBy('position.title');

        return view('public.candidates', compact('election', 'candidates'));
    }
}