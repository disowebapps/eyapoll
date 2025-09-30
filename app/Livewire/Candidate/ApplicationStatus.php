<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use App\Models\Candidate\Candidate;
use App\Models\Candidate\CandidateDocument;
use App\Models\Candidate\PaymentHistory;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ApplicationStatus extends Component
{
    use AuthorizesRequests;

    public Candidate $application;
    public $documents = [];
    public $paymentInfo = null;
    public $statusBadgeColor = '';
    public $showWithdrawModal = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount($candidate)
    {
        if (is_numeric($candidate)) {
            $this->application = Candidate::with(['election', 'position', 'user', 'approver', 'documents', 'paymentHistory'])->findOrFail($candidate);
        } else {
            $this->application = $candidate;
        }

        $this->authorize('view', $this->application);

        // Ensure the application belongs to the current user
        if ($this->application->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to view this application.');
        }

        $this->loadDocuments();
        $this->loadPaymentInfo();
        $this->setStatusBadgeColor();
    }

    public function loadDocuments()
    {
        $this->documents = $this->application->documents->map(function ($document) {
            return [
                'id' => $document->id,
                'filename' => $document->original_filename,
                'type' => $document->document_type,
                'file_size' => $document->file_size,
                'uploaded_at' => $document->created_at,
                'status' => $document->status,
            ];
        })->toArray();
    }

    public function loadPaymentInfo()
    {
        if ($this->application->application_fee > 0) {
            $latestPayment = $this->application->paymentHistory()->latest()->first();

            $this->paymentInfo = [
                'amount' => $this->application->application_fee,
                'status' => $this->application->payment_status->label(),
                'can_pay' => $this->application->payment_status->value === 'pending',
                'reference' => $latestPayment ? $latestPayment->reference : null,
            ];
        }
    }

    public function setStatusBadgeColor()
    {
        $this->statusBadgeColor = match ($this->application->status->value) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'suspended' => 'red',
            'withdrawn' => 'gray',
            default => 'gray',
        };
    }

    public function downloadDocument($documentId)
    {
        $document = CandidateDocument::findOrFail($documentId);

        // Ensure the document belongs to the current user's application
        if ($document->candidate_id !== $this->application->id) {
            abort(403, 'You do not have permission to download this document.');
        }

        if (!Storage::disk('public')->exists($document->filename)) {
            session()->flash('error', 'Document file not found.');
            return;
        }

        return Storage::disk('public')->download($document->filename, $document->original_filename);
    }

    public function processPayment()
    {
        if (!$this->paymentInfo || !$this->paymentInfo['can_pay']) {
            session()->flash('error', 'Payment cannot be processed at this time.');
            return;
        }

        try {
            $paymentService = app(PaymentService::class);

            $paymentResult = $paymentService->processMockPayment([
                'amount' => $this->application->application_fee,
                'candidate_id' => $this->application->id,
                'description' => "Application fee for {$this->application->election->title}",
            ]);

            if ($paymentResult['success']) {
                $this->application->update([
                    'payment_status' => 'paid',
                    'payment_reference' => $paymentResult['reference'],
                ]);

                PaymentHistory::create([
                    'candidate_id' => $this->application->id,
                    'amount' => $this->application->application_fee,
                    'payment_method' => 'mock',
                    'reference' => $paymentResult['reference'],
                    'status' => 'completed',
                ]);

                Log::info('Candidate payment processed successfully', [
                    'candidate_id' => $this->application->id,
                    'reference' => $paymentResult['reference'],
                    'amount' => $this->application->application_fee,
                ]);

                session()->flash('success', 'Payment processed successfully!');
                $this->loadPaymentInfo();
            } else {
                Log::warning('Candidate payment failed', [
                    'candidate_id' => $this->application->id,
                    'error' => $paymentResult['error'] ?? 'Unknown error',
                ]);

                session()->flash('error', $paymentResult['message'] ?? 'Payment failed. Please try again.');
            }

        } catch (\Exception $e) {
            Log::error('Payment processing error', [
                'candidate_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'An error occurred while processing payment. Please try again.');
        }
    }

    public function withdrawApplication()
    {
        if (!$this->application->canWithdraw()) {
            session()->flash('error', 'Application cannot be withdrawn at this time.');
            return;
        }

        try {
            $oldStatus = $this->application->status;

            $this->application->update(['status' => 'withdrawn']);

            // Add audit log
            \App\Models\Candidate\CandidateActionHistory::create([
                'candidate_id' => $this->application->id,
                'action' => 'withdrawn',
                'performed_by' => auth()->id(),
                'reason' => 'Candidate self-withdrawal',
                'old_status' => $oldStatus,
                'new_status' => 'withdrawn',
            ]);

            Log::info('Candidate application withdrawn', [
                'candidate_id' => $this->application->id,
                'user_id' => auth()->id(),
            ]);

            session()->flash('success', 'Application withdrawn successfully.');
            $this->setStatusBadgeColor();
            $this->showWithdrawModal = false;

        } catch (\Exception $e) {
            Log::error('Application withdrawal failed', [
                'candidate_id' => $this->application->id,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Failed to withdraw application. Please try again.');
        }
    }

    public function openWithdrawModal()
    {
        $this->showWithdrawModal = true;
    }

    public function closeWithdrawModal()
    {
        $this->showWithdrawModal = false;
    }

    public function refreshData()
    {
        $this->application->refresh();
        $this->loadDocuments();
        $this->loadPaymentInfo();
        $this->setStatusBadgeColor();
    }

    public function render()
    {
        return view('livewire.candidate.application-status');
    }
}