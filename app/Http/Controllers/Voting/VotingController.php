<?php

namespace App\Http\Controllers\Voting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Election\Election;
use App\Models\Voting\VoteAuthorization;
use App\Services\Voting\VoteAuthorizationService;
use App\Services\Voting\VotingService;
use App\Services\Voting\VoteActivityTracker;

class VotingController extends Controller
{
    public function __construct(
        private VoteAuthorizationService $authService,
        private VotingService $votingService,
        private VoteActivityTracker $activityTracker
    ) {}

    public function requestAuthorization(Request $request, Election $election)
    {
        $user = $request->user();
        
        $result = $this->authService->authorizeVote($user, $election);
        
        if ($result['status'] === 'denied') {
            return response()->json([
                'error' => 'authorization_denied',
                'reasons' => $result['reasons']
            ], 403);
        }

        return response()->json([
            'authorization' => [
                'id' => $result['authorization']->id,
                'token' => $result['authorization']->auth_token,
                'expires_at' => $result['authorization']->expires_at,
                'time_left' => $result['authorization']->timeLeft(),
                'election_id' => $election->id
            ]
        ]);
    }

    public function castVote(Request $request)
    {
        $request->validate([
            'auth_id' => 'required|exists:vote_authorizations,id',
            'selections' => 'required|array'
        ]);

        $auth = VoteAuthorization::find($request->auth_id);

        if ($auth->hasExpired()) {
            $recovery = $this->authService->handleExpiredAuthorization(
                $auth->voter_hash,
                $auth->election_id
            );

            if ($recovery['status'] === 'recovered') {
                return response()->json([
                    'status' => 'session_recovered',
                    'authorization' => [
                        'id' => $recovery['authorization']->id,
                        'token' => $recovery['authorization']->auth_token,
                        'expires_at' => $recovery['authorization']->expires_at,
                        'time_left' => $recovery['authorization']->timeLeft()
                    ],
                    'draft' => $recovery['draft']
                ]);
            }

            return response()->json([
                'error' => 'session_expired',
                'recovery_status' => $recovery['status']
            ], 401);
        }

        $result = $this->votingService->castVote($auth, $request->selections);

        if ($result['status'] === 'failed') {
            return response()->json([
                'error' => 'vote_failed',
                'reason' => $result['reason']
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'receipt' => $result['receipt']
        ]);
    }

    public function trackActivity(Request $request)
    {
        $auth = VoteAuthorization::find($request->auth_id);

        if (!$auth || !$auth->isValid()) {
            return response()->json(['error' => 'invalid_authorization'], 401);
        }

        $result = $this->activityTracker->trackActivity(
            $auth,
            $request->action,
            $request->data ?? []
        );

        return response()->json($result);
    }
}