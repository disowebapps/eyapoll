<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Election\Election;
use App\Events\ElectionResultsPublished;
use App\Enums\Election\ElectionPhase;

class AutoResumeRegistration extends Command
{
    protected $signature = 'voter:auto-resume';
    protected $description = 'Auto-resume voter registration for elections with published results';

    public function handle()
    {
        $elections = Election::where('phase', ElectionPhase::RESULTS_PUBLISHED)
            ->whereNotNull('voter_register_published')
            ->whereNull('registration_resumed_at')
            ->get();

        foreach ($elections as $election) {
            ElectionResultsPublished::dispatch($election);
            $election->update(['registration_resumed_at' => now()]);
            $this->info("Resumed registration for election: {$election->title}");
        }

        return 0;
    }
}