<?php

namespace App\Http\Controllers;

use App\Models\Voting\VoteRecord;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function verify(Request $request, string $hash)
    {
        $vote = VoteRecord::where('receipt_hash', $hash)->first();
        
        if (!$vote) {
            return view('public.verify-receipt', [
                'hash' => $hash,
                'status' => 'not_found'
            ]);
        }

        return view('public.verify-receipt', [
            'hash' => $hash,
            'status' => 'verified',
            'vote' => [
                'election_id' => $vote->election_id,
                'cast_at' => $vote->cast_at,
                'chain_position' => $this->getChainPosition($vote)
            ]
        ]);
    }

    private function getChainPosition(VoteRecord $vote): int
    {
        return VoteRecord::where('election_id', $vote->election_id)
            ->where('cast_at', '<=', $vote->cast_at)
            ->count();
    }
}