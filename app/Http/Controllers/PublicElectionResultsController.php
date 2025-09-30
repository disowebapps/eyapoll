<?php

namespace App\Http\Controllers;

use App\Models\Election\Election;
use App\Enums\Election\ElectionStatus;
use App\Services\Timer\UnifiedTimerService;
use Illuminate\Http\Request;

class PublicElectionResultsController extends Controller
{
    public function __construct(
        private UnifiedTimerService $timerService
    ) {}

    public function index()
    {
        $activeElections = Election::active()
            ->with(['positions', 'candidates'])
            ->orderBy('ends_at', 'asc')
            ->get();

        $completedElections = Election::ended()
            ->where('results_published', true)
            ->with(['positions', 'candidates', 'voteTallies'])
            ->orderBy('ends_at', 'desc')
            ->get();

        // Add timer data for each active election
        $activeElections->each(function ($election) {
            $election->alpine_timer = $this->timerService->getAlpineTimerConfig($election);
        });

        return view('public.election-results', compact('activeElections', 'completedElections'));
    }
}