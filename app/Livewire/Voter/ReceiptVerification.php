<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use App\Services\Voting\VotingService;
use App\Services\Voting\VoterHashService;
use App\Models\Election\Election;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReceiptVerification extends Component
{
    use AuthorizesRequests;

    public Election $election;
    public $receiptData = null;
    public $loading = true;

    public function mount($election)
    {
        try {
            if (is_numeric($election)) {
                $this->election = Election::findOrFail($election);
            } else {
                $this->election = $election;
            }

            $this->authorize('view', $this->election);
            $this->loadReceipt();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Receipt access attempt for non-existent election', [
                'election_id' => $election,
                'user_id' => Auth::id()
            ]);
            abort(404);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized receipt access attempt', [
                'election_id' => $election,
                'user_id' => Auth::id()
            ]);
            abort(403);
        } catch (\Exception $e) {
            Log::error('Receipt verification mount failed', [
                'election_id' => $election,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            abort(500);
        }
    }

    public function loadReceipt()
    {
        $this->loading = true;

        try {
            $votingService = app(VotingService::class);
            $voterHashService = app(VoterHashService::class);
            $voterHash = $voterHashService->generateVoterHash(Auth::user(), $this->election);
            
            Log::info('Receipt access attempt', [
                'user_id' => Auth::id(),
                'election_id' => $this->election->id,
                'voter_hash' => substr($voterHash, 0, 8) . '...'
            ]);
            
            $this->receiptData = $votingService->getVoterReceipt($voterHash, $this->election);

            if (!$this->receiptData) {
                Log::info('Receipt not found', [
                    'user_id' => Auth::id(),
                    'election_id' => $this->election->id
                ]);
                session()->flash('error', 'No receipt found for this election. You may not have voted yet.');
                return redirect()->route('voter.dashboard');
            }
            
            Log::info('Receipt accessed successfully', [
                'user_id' => Auth::id(),
                'election_id' => $this->election->id
            ]);
            
        } catch (\InvalidArgumentException $e) {
            Log::warning('Receipt access validation failed', [
                'user_id' => Auth::id(),
                'election_id' => $this->election->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Invalid receipt request.');
        } catch (\Exception $e) {
            Log::error('Receipt access failed', [
                'user_id' => Auth::id(),
                'election_id' => $this->election->id,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Unable to load receipt. Please try again.');
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.voter.receipt-verification');
    }
}