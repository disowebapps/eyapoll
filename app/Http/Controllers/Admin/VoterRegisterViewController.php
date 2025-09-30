<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Election\Election;

class VoterRegisterViewController extends Controller
{
    public function index(Election $election)
    {
        $voters = $election->voteTokens()
            ->with('user')
            ->paginate(20);

        return view('admin.elections.voter-register', compact('election', 'voters'));
    }
}