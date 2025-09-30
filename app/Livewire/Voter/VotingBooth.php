<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use App\Models\Election\Election;
use App\Services\Voting\VoteAuthorizationService;
use App\Services\Voting\VotingService;
use App\Services\Voting\BallotDraftService;
use App\Services\Voting\VoteActivityTracker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use App\Models\Voting\VotingSession;
use Illuminate\Support\Str;

class VotingBooth extends Component
{
    public Election $election;
    public $authorization;
    
    #[Locked]
    public $positions = [];
    public $currentPositionIndex = 0;
    public $selections = [];
    public $showConfirmation = false;
    public $isSubmitting = false;
    public $progress = [];
    public $voteSummary = [];
    public $canSubmit = false;
    public $timeLeft = 0;
    public $showSecurityLoader = false;
    public $sessionId;
    
    // Performance optimization: cache services and counts
    private $authService;
    private $votingService;
    private $activityTracker;
    private $draftService;
    
    #[Locked]
    public $positionsCount = 0;
    private $expiryTimestamp;
    
    protected $listeners = ['refresh' => '$refresh'];

    // Rate limiting for authorization extensions
    private $extensionAttempts = [];
    private const MAX_EXTENSIONS_PER_MINUTE = 5;
    private const EXTENSION_WINDOW_SECONDS = 60;

    public function boot()
    {
        $this->authService = app(VoteAuthorizationService::class);
        $this->votingService = app(VotingService::class);
        $this->activityTracker = app(VoteActivityTracker::class);
        $this->draftService = app(BallotDraftService::class);
    }

    public function mount(Election $election)
    {
        $this->election = $election;
        $this->sessionId = session()->getId() . '_' . Str::random(8);

        try {
            $this->requestAuthorization();
            $this->loadPositions();
            $this->restoreSession();
            $this->updateProgress();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to initialize voting booth');
            return redirect()->route('voter.dashboard');
        }
    }

    /**
     * Check rate limit for authorization extensions
     */
    private function checkExtensionRateLimit(): void
    {
        $userId = Auth::id();
        $now = now()->timestamp;

        // Clean old attempts
        $this->extensionAttempts = array_filter($this->extensionAttempts, function($timestamp) use ($now) {
            return ($now - $timestamp) < self::EXTENSION_WINDOW_SECONDS;
        });

        // Count attempts in current window
        $recentAttempts = count($this->extensionAttempts);

        if ($recentAttempts >= self::MAX_EXTENSIONS_PER_MINUTE) {
            Log::warning('Rate limit exceeded for authorization extensions', [
                'user_id' => $userId,
                'attempts' => $recentAttempts,
                'election_id' => $this->election->id
            ]);
            throw new \App\Exceptions\RateLimitException('Too many authorization extensions. Please wait before trying again.');
        }

        // Record this attempt
        $this->extensionAttempts[] = $now;
    }

    /**
     * Extend authorization with rate limiting
     */
    private function extendAuthorization(): void
    {
        $this->checkExtensionRateLimit();
        $this->authorization->update(['expires_at' => now()->addMinutes(30)]);
    }

    public function requestAuthorization()
    {
        $result = $this->authService->authorizeVote(Auth::user(), $this->election);

        if ($result['status'] === 'denied') {
            $errorMessage = implode(', ', $result['reasons']);
            if (isset($result['receipt_url'])) {
                // Security fix: validate receipt URL is internal
                $receiptUrl = $result['receipt_url'];
                if (filter_var($receiptUrl, FILTER_VALIDATE_URL) && parse_url($receiptUrl, PHP_URL_HOST) === request()->getHost()) {
                    $errorMessage .= ' <a href="' . e($receiptUrl) . '" class="inline-flex items-center ml-2 px-3 py-1 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">View Receipt</a>';
                }
            }
            session()->flash('error', $errorMessage);
            return $this->redirectRoute('voter.dashboard');
        }

        $this->authorization = $result['authorization'];
        $this->timeLeft = $this->authorization->timeLeft();
        $this->expiryTimestamp = $this->authorization->expires_at->timestamp;
        
        $draft = $this->draftService->restoreDraft(
            $this->authorization->voter_hash,
            $this->election->id
        );
        
        if ($draft) {
            $this->selections = $draft['selections'] ?? [];
            $this->currentPositionIndex = $draft['current_position'] ?? 0;
            $this->updateProgress();
        }
    }

    public function loadPositions()
    {
        try {
            $positions = $this->election->positions()
                ->with(['candidates' => function($query) {
                    $query->where('status', 'approved')
                          ->with('user:id,first_name,last_name');
                }])
                ->get();

            Log::info('Positions loaded with eager loading', ['count' => $positions->count()]);

            $this->positions = [];
            foreach ($positions as $position) {
                $candidates = [];
                foreach ($position->candidates as $candidate) {
                    $candidates[] = [
                        'id' => $candidate->id,
                        'name' => $candidate->user ? $candidate->user->full_name : 'Unknown Candidate'
                    ];
                }

                $this->positions[] = [
                    'id' => $position->id,
                    'title' => $position->title,
                    'max_selections' => $position->max_selections ?? 1,
                    'candidates' => $candidates
                ];
            }

            $this->positionsCount = count($this->positions);
            Log::info('Positions processed', ['positions_count' => $this->positionsCount]);

        } catch (\Exception $e) {
            Log::error('Failed to load positions', ['error' => $e->getMessage()]);
            $this->positions = [];
            $this->positionsCount = 0;
        }
    }



    public function toggleCandidate($positionId, $candidateId)
    {
        $this->extendAuthorization();

        $positionId = (int) $positionId;
        $candidateId = (int) $candidateId;
        
        if (!isset($this->selections[$positionId])) {
            $this->selections[$positionId] = [];
        }

        $isSelected = in_array($candidateId, $this->selections[$positionId]);
        $hasOtherSelection = !empty($this->selections[$positionId]) && !$isSelected;
        
        if ($hasOtherSelection) {
            session()->flash('vote_message', 'Please unselect your current choice first to vote for another candidate.');
            return;
        }
        
        if ($isSelected) {
            $this->selections[$positionId] = [];
        } else {
            $this->selections[$positionId] = [$candidateId];
        }

        $this->updateProgress();
        $this->saveSession();
        
        if ($this->activityTracker && $this->authorization) {
            $this->activityTracker->trackActivity(
                $this->authorization,
                'selection_made',
                [
                    'position_id' => $positionId, 
                    'candidate_id' => $candidateId,
                    'current_position' => $this->currentPositionIndex
                ]
            );
        }
    }

    public function abstainPosition($positionId)
    {
        $this->selections[$positionId] = [];
        $this->updateProgress();
        
        $this->activityTracker->trackActivity(
            $this->authorization,
            'selection_made',
            [
                'position_id' => $positionId, 
                'abstain' => true,
                'current_position' => $this->currentPositionIndex
            ]
        );
        
        if ($this->currentPositionIndex < $this->positionsCount - 1) {
            $this->nextPosition();
        }
    }

    public function nextPosition()
    {
        // Extend authorization with rate limiting
        $this->extendAuthorization();

        if ($this->currentPositionIndex < $this->positionsCount - 1) {
            $this->currentPositionIndex++;
            $this->saveSession();
        }
    }

    public function previousPosition()
    {
        if ($this->currentPositionIndex > 0) {
            $this->currentPositionIndex--;
        }
    }

    public function goToPosition($index)
    {
        if ($index >= 0 && $index < $this->positionsCount) {
            $this->currentPositionIndex = $index;
        }
    }

    public function submitVote()
    {
        $this->isSubmitting = true;
        
        if (!$this->isAuthorizationValid()) {
            session()->flash('error', 'Session expired. Please try voting again.');
            $this->isSubmitting = false;
            return $this->redirectRoute('voter.dashboard');
        }

        $result = $this->votingService->castVote($this->authorization, $this->selections);
        
        if ($result['status'] === 'success') {
            $this->draftService->clearDraft(
                $this->authorization->voter_hash,
                $this->election->id
            );
            
            return $this->redirectRoute('voter.receipt', ['election' => $this->election->id]);
        }

        session()->flash('error', $result['reason']);
        $this->isSubmitting = false;
    }



    private function updateProgress()
    {
        $completed = 0;
        foreach ($this->selections as $selection) {
            if (!empty($selection)) {
                $completed++;
            }
        }
        
        $this->progress = [
            'completed' => $completed,
            'total' => $this->positionsCount
        ];
        
        $this->canSubmit = $completed > 0;
    }

    public function getCurrentPositionProperty()
    {
        return $this->positions[$this->currentPositionIndex] ?? null;
    }

    public function getCurrentPosition()
    {
        return $this->getCurrentPositionProperty();
    }

    public function getSelectedCandidatesForPosition($positionId)
    {
        return $this->selections[$positionId] ?? [];
    }

    public function isPositionComplete($positionId)
    {
        return !empty($this->selections[$positionId]);
    }

    public function showConfirmationModal()
    {
        // Extend authorization with rate limiting
        $this->extendAuthorization();

        $this->voteSummary = $this->generateVoteSummary();
        $this->showConfirmation = true;
    }

    public function hideConfirmationModal()
    {
        $this->showConfirmation = false;
    }

    public function showAbstainModal()
    {
        $this->abstainPosition($this->currentPosition['id']);
    }

    private function generateVoteSummary()
    {
        $summary = [];
        
        foreach ($this->positions as $position) {
            $selectedIds = $this->selections[$position['id']] ?? [];
            $selectedCandidates = [];
            
            if ($selectedIds) {
                $candidateMap = array_column($position['candidates'], 'name', 'id');
                foreach ($selectedIds as $id) {
                    if (isset($candidateMap[$id])) {
                        $selectedCandidates[] = $candidateMap[$id];
                    }
                }
            }
            
            $summary[] = [
                'position' => $position['title'],
                'selections' => $selectedCandidates,
                'is_abstention' => empty($selectedCandidates)
            ];
        }
        
        return $summary;
    }

    private function isAuthorizationValid()
    {
        return $this->authorization && 
               $this->authorization->expires_at && 
               $this->authorization->expires_at->isFuture();
    }

    private function saveSession()
    {
        if ($this->authorization) {
            VotingSession::createOrUpdate(
                $this->sessionId,
                $this->authorization->voter_hash,
                $this->election->id,
                [
                    'selections' => $this->selections,
                    'current_position_index' => $this->currentPositionIndex,
                    'progress' => $this->progress
                ]
            );
        }
    }

    private function restoreSession()
    {
        if ($this->authorization) {
            $session = VotingSession::where('voter_hash', $this->authorization->voter_hash)
                ->where('election_id', $this->election->id)
                ->latest()
                ->first();
                
            if ($session) {
                $this->selections = $session->selections ?? [];
                $this->currentPositionIndex = $session->current_position_index ?? 0;
                $this->progress = $session->progress ?? [];
            }
        }
    }

    public function render()
    {
        return view('livewire.voter.voting-booth', [
            'currentPosition' => $this->getCurrentPositionProperty()
        ]);
    }
}