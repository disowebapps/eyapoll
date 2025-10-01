<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Candidate\Candidate;
use App\Services\Candidate\CandidateService;
use App\Models\Admin;
use App\Http\Requests\Admin\CandidateSuspendRequest;
use App\Http\Requests\Admin\CandidateActionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CandidateController extends Controller
{
    private function successResponse(string $message): RedirectResponse
    {
        return redirect()->back()->with('success', $message);
    }

    private function errorResponse(Throwable $e, string $message): RedirectResponse
    {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message . ': ' . $e->getMessage());
    }

    public function __construct(
        private CandidateService $candidateService
    ) {}

    private function getAuthenticatedAdmin(): Admin
    {
        /** @var Admin|null $admin */
        $admin = Admin::find(Auth::guard('admin')->id());
        if (!$admin) {
            throw new \RuntimeException('Admin not found');
        }
        return $admin;
    }

    public function show(Candidate $candidate): \Illuminate\Contracts\View\View
    {
        // Validation log: Confirm Log facade is accessible
        Log::debug('CandidateController show method called', ['candidate_id' => $candidate->id]);

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
        $applicationProgress = Cache::remember(
            "candidate_progress_{$candidate->id}",
            300,
            function() use ($candidate) {
                try {
                    return $candidate->getApplicationProgress();
                } catch (\Exception $e) {
                                        Log::warning('Error calculating progress', [
                        'candidate_id' => $candidate->id,
                        'error' => $e->getMessage()
                    ]);
                    return ['steps' => [], 'completed' => 0, 'total' => 4, 'percentage' => 0];
                }
            }
        );
        
        $voteStats = Cache::remember(
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
                                        Log::warning('Error calculating vote stats', [
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

    public function update(Request $request, Candidate $candidate): RedirectResponse
    {
        $validated = $request->validate([
            'manifesto' => 'required|string|min:10|max:5000',
            'application_fee' => 'required|numeric|min:0|max:1000000',
        ]);

        try {
            $admin = $this->getAuthenticatedAdmin();
            $this->candidateService->editCandidate($candidate, $admin, $validated);
            
            Log::info('Candidate updated successfully', [
                'candidate_id' => $candidate->id,
                'admin_id' => $admin->getKey(),
                'fields' => array_keys($validated)
            ]);
            
            return redirect()
                ->route('admin.candidates.show', $candidate)
                ->with('success', 'Candidate updated successfully');
        } catch (Throwable $e) {
            Log::error('Failed to update candidate', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($e, 'Failed to update candidate');
        }
    }

    public function approve(Candidate $candidate): RedirectResponse
    {
        try {
            $admin = $this->getAuthenticatedAdmin();
            $this->candidateService->approveCandidate($candidate, $admin, '');
            
            Log::info('Candidate approved successfully', [
                'candidate_id' => $candidate->id,
                'admin_id' => $admin->getKey()
            ]);
            
            return $this->successResponse('Candidate approved successfully');
        } catch (Throwable $e) {
            Log::error('Failed to approve candidate', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($e, 'Failed to approve candidate');
        }
    }

    public function reject(CandidateActionRequest $request, Candidate $candidate): RedirectResponse
    {
        try {
            $admin = $this->getAuthenticatedAdmin();
            $this->candidateService->rejectCandidate($candidate, $admin, $request->validated('reason'));
            
            Log::info('Candidate rejected successfully', [
                'candidate_id' => $candidate->id,
                'admin_id' => $admin->getKey()
            ]);
            
            return $this->successResponse('Candidate rejected successfully');
        } catch (Throwable $e) {
            Log::error('Failed to reject candidate', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($e, 'Failed to reject candidate');
        }
    }

    public function suspend(CandidateSuspendRequest $request, Candidate $candidate): RedirectResponse
    {
        try {
            Log::info('Suspend attempt started', [
                'candidate_id' => $candidate->id,
                'reason' => $request->validated('reason')
            ]);
            
            $admin = $this->getAuthenticatedAdmin();
            $result = $this->candidateService->suspendCandidate($candidate, $admin, $request->validated('reason'));
            
            Log::info('Candidate suspended successfully', [
                'candidate_id' => $candidate->id,
                'admin_id' => $admin->getKey(),
                'result' => $result
            ]);
            
            return $this->successResponse('Candidate suspended successfully');
        } catch (Throwable $e) {
            Log::error('Candidate suspension failed', [
                'candidate_id' => $candidate->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse($e, 'Failed to suspend candidate');
        }
    }

    public function unsuspend(CandidateSuspendRequest $request, Candidate $candidate): RedirectResponse
    {
        try {
            $admin = $this->getAuthenticatedAdmin();
            $this->candidateService->unsuspendCandidate($candidate, $admin, $request->validated('reason'));
            
            Log::info('Candidate unsuspended successfully', [
                'candidate_id' => $candidate->id,
                'admin_id' => $admin->getKey()
            ]);
            
            return $this->successResponse('Candidate unsuspended successfully');
        } catch (Throwable $e) {
            Log::error('Failed to unsuspend candidate', [
                'candidate_id' => $candidate->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($e, 'Failed to unsuspend candidate');
        }
    }



    public function downloadPaymentProof(Candidate $candidate, int $proofId): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $proof = $candidate->paymentProofs()->findOrFail($proofId);
        $path = storage_path('app/private/payment-proofs/' . $proof->filename);
        
        if (!file_exists($path)) {
            abort(404, 'Payment proof file not found');
        }
        
        return response()->download($path, $proof->original_filename);
    }
}