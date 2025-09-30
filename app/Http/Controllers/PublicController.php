<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicController extends Controller
{
    public function candidates(Request $request)
    {
        $search = $request->get('search');
        $election = $request->get('election');
        $position = $request->get('position');

        $candidates = DB::table('candidates')
            ->join('elections', 'candidates.election_id', '=', 'elections.id')
            ->join('positions', 'candidates.position_id', '=', 'positions.id')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->select(
                'candidates.*',
                'elections.title as election_title',
                'positions.title as position_title',
                'users.first_name',
                'users.last_name'
            )
            ->where('candidates.status', 'approved')
            ->whereNotNull('candidates.user_id')
            ->when($search, function($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('users.first_name', 'like', "%{$search}%")
                          ->orWhere('users.last_name', 'like', "%{$search}%")
                          ->orWhere('candidates.manifesto', 'like', "%{$search}%");
                });
            })
            ->when($election, fn($q) => $q->where('candidates.election_id', $election))
            ->when($position, fn($q) => $q->where('candidates.position_id', $position))
            ->orderBy('elections.starts_at', 'desc')
            ->orderBy('positions.order_index')
            ->paginate(12);

        $elections = DB::table('elections')
            ->where('status', '!=', 'cancelled')
            ->orderBy('starts_at', 'desc')
            ->get();

        $positions = DB::table('positions')
            ->join('elections', 'positions.election_id', '=', 'elections.id')
            ->select('positions.*', 'elections.title as election_title')
            ->orderBy('elections.starts_at', 'desc')
            ->orderBy('positions.order_index')
            ->get();

        return view('public.candidates', compact('candidates', 'elections', 'positions', 'search', 'election', 'position'));
    }

    public function candidate($id)
    {
        $candidate = DB::table('candidates')
            ->join('elections', 'candidates.election_id', '=', 'elections.id')
            ->join('positions', 'candidates.position_id', '=', 'positions.id')
            ->join('users', 'candidates.user_id', '=', 'users.id')
            ->select(
                'candidates.*',
                'elections.title as election_title',
                'positions.title as position_title',
                'users.first_name',
                'users.last_name',
                'users.email'
            )
            ->where('candidates.id', $id)
            ->where('candidates.status', 'approved')
            ->first();

        if (!$candidate) {
            abort(404);
        }

        return view('public.candidate', compact('candidate'));
    }
}