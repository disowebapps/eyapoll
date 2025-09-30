<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all votes ordered by cast_at for each election
        $elections = DB::table('votes')->distinct()->pluck('election_id');
        
        foreach ($elections as $electionId) {
            $votes = DB::table('votes')
                ->where('election_id', $electionId)
                ->orderBy('cast_at')
                ->get(['id', 'chain_hash']);
            
            $previousHash = null;
            foreach ($votes as $vote) {
                DB::table('votes')
                    ->where('id', $vote->id)
                    ->update(['previous_hash' => $previousHash]);
                
                $previousHash = $vote->chain_hash;
            }
        }
    }

    public function down(): void
    {
        DB::table('votes')->update(['previous_hash' => null]);
    }
};