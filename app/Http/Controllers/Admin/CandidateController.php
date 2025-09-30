<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate\Candidate;
use App\Services\Candidate\CandidateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CandidateController extends Controller
{
    public function __construct(
        private CandidateService $candidateService
    ) {}

    public function show(Candidate $candidate)
    {
        
        // Performance: Load only required fields with relationships
        $candidate->loadMissing([
            'user:id,first_name,last_name,email,phone_number,created_at',
            'election:id,title,type,status,starts_at,ends_at',
            'position:id,title,description,max_selections',
            'documents:id,candidate_id,document_type,status,created_at,reviewed_at,reviewed_by',
            'documents.reviewer:id,first_name,last_name',
            'voteTallies:id,candidate_id,vote_count',
            'approver:id,first_name,last_name',
            'suspender:id,first_name,last_name',
            'actionHistory.admin:id,first_name,last_name'
        ]);

        // Edge case: Handle missing relationships gracefully
        if (!$candidate->user || !$candidate->election || !$candidate->position) {
            abort(404, 'Candidate data incomplete');
        }

        // Performance: Cache expensive calculations with error handling
        $applicationProgress = \Illuminate\Support\Facades\Cache::remember(
            "candidate_progress_{$candidate->id}",
            300,
            function() use ($candidate) {
                try {
                    return $candidate->getApplicationProgress();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Error calculating progress', [
                        'candidate_id' => $candidate->id,
                        'error' => $e->getMessage()
                    ]);
                    return ['steps' => [], 'completed' => 0, 'total' => 4, 'percentage' => 0];
                }
            }
        );
        
        $voteStats = \Illuminate\Support\Facades\Cache::remember(
            "candidate_votes_{$candidate->id}",
            60,
            function() use ($candidate) {
                try {
                    return [
                        'vote_count' => $candidate->getVoteCount(),
                        'vote_percentage' => $candidate->getVotePercentage(),
                        'ranking' => $candidate->getRanking(),
                        'is_winner' => $candidate->isWinner(),
                    ];
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Error calculating vote stats', [
                        'candidate_id' => $candidate->id,
                        'error' => $e->getMessage()
                    ]);
                    return ['vote_count' => 0, 'vote_percentage' => 0, 'ranking' => 1, 'is_winner' => false];
                }
            }
        );

        return view('admin.candidates.show', compact('candidate', 'applicationProgress', 'voteStats'));
    }

    public function edit(Candidate $candidate)
    {
        return view('admin.candidates.edit', compact('candidate'));
    }

    public function update(Request $request, Candidate $candidate)
    {
        $validated = $request->validate([
            'manifesto' => 'required|string|min:10|max:5000',
            'application_fee' => 'required|numeric|min:0|max:1000000',
        ]);

        try {
            $admin = Auth::guard('admin')->user();
            $this->candidateService->editCandidate($candidate, $admin, $validated);
            
            return redirect()->route('admin.candidates.show', $candidate)
                ->with('success', 'Candidate updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function approve(Candidate $candidate)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $this->candidateService->approveCandidate($candidate, $admin, '');
            
            return redirect()->back()->with('success', 'Candidate approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to approve candidate: ' . $e->getMessage());
        }
    }

    public function reject(\App\Http\Requests\Admin\CandidateActionRequest $request, Candidate $candidate)
    {
        try {
            $admin = Auth::guard('admin')->user();
            $this->candidateService->rejectCandidate($candidate, $admin, $request->reason);
            
            return redirect()->back()->with('success', 'Candidate rejected successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to reject candidate: ' . $e->getMessage());
        }
    }

    public function suspend(Request $request, Candidate $candidate)
    {
        try {
            \Illuminate\Support\Facades\Log::info('Suspend attempt started', [
                'candidate_id' => $candidate->id,
                'reason' => $request->input('reason'),
                'all_input' => $request->all()
            ]);
            
            if (!$request->filled('reason')) {
                throw new \InvalidArgumentException('Reason is required');
            }
            
            $admin = Auth::guard('admin')->user();
            
            \Illuminate\Support\Facades\Log::info('Admin retrieved', [
                'admin_id' => $admin->id
            ]);
            
            $result = $this->candidateService->suspendCandidate($candidate, $admin, $request->input('reason'));
            
            \Illuminate\Support\Facades\Log::info('Suspend result', [
                'result' => $result
            ]);
            
            return redirect()->back()->with('success', 'Candidate suspended successfully');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Candidate suspension failed', [
                'candidate_id' => $candidate->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Failed to suspend candidate: ' . $e->getMessage());
        }
    }

    public function unsuspend(Request $request, Candidate $candidate)
    {
        try {
            if (!$request->filled('reason')) {
                throw new \InvalidArgumentException('Reason is required');
            }
            
            $admin = Auth::guard('admin')->user();
            $this->candidateService->unsuspendCandidate($candidate, $admin, $request->input('reason'));
            
            return redirect()->back()->with('success', 'Candidate unsuspended successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to unsuspend candidate: ' . $e->getMessage());
        }
    }



    public function downloadPaymentProof(Candidate $candidate, $proofId)
    {
        $proof = $candidate->paymentProofs()->findOrFail($proofId);
        $path = storage_path('app/private/payment-proofs/' . $proof->filename);
        
        if (!file_exists($path)) {
            abort(404, 'Payment proof file not found');
        }
        
        return response()->download($path, $proof->original_filename);
    }
}