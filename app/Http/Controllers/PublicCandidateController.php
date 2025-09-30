<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Enums\Auth\UserRole;
use App\Enums\Auth\UserStatus;

class PublicCandidateController extends Controller
{
    public function index(Request $request)
    {
        Log::info('PublicCandidateController@index called', [
            'request_data' => $request->all()
        ]);

        $query = User::where('role', UserRole::CANDIDATE)
                    ->where('status', UserStatus::APPROVED);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $candidates = $query->paginate(12);
        $elections = Election::all();
        $positions = Position::with('election')->get();

        Log::info('PublicCandidateController@index data prepared', [
            'candidates_count' => $candidates->count(),
            'total_candidates' => $candidates->total(),
            'elections_count' => $elections->count(),
            'positions_count' => $positions->count()
        ]);

        return view('public.candidates', [
            'candidates' => $candidates,
            'elections' => $elections,
            'positions' => $positions,
            'search' => $request->search ?? ''
        ]);
    }

    public function show($id)
    {
        $candidate = User::where('role', UserRole::CANDIDATE)
                        ->where('status', 'approved')
                        ->findOrFail($id);

        return view('public.candidate', compact('candidate'));
    }
}