<?php

namespace App\Livewire\Admin;

use Livewire\WithPagination;
use App\Models\Candidate\CandidateDocument;
use App\Models\Candidate\Candidate;
use App\Services\Auth\MFAService;
use App\Services\AuditLoggingService;
use App\Services\DocumentManagementService;
use App\Services\ReviewManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentReview extends BaseAdminComponent
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $statusFilter = 'pending';
    public $documentTypeFilter = 'all';
    public $showDocumentModal = false;
    public $selectedDocument = null;
    public $reviewNotes = '';
    public $reviewAction = '';

    // Assignment properties
    public $showAssignModal = false;
    public $assignDocumentId = null;
    public $selectedReviewerId = null;
    public $availableReviewers = [];

    // Bulk operations
    public $selectedDocuments = [];
    public $selectAll = false;
    public $bulkAction = '';
    public $bulkRejectionReason = '';

    protected $listeners = ['refreshDocuments' => '$refresh'];

    public function mount()
    {
        \Illuminate\Support\Facades\Log::info('DocumentReview::mount called');
        $this->authorize('viewAny', CandidateDocument::class);
        \Illuminate\Support\Facades\Log::info('DocumentReview::mount - Authorization passed');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewDocument($documentId)
    {
        $this->selectedDocument = CandidateDocument::with(['candidate.user', 'candidate.election', 'candidate.position'])
            ->findOrFail($documentId);

        $this->authorize('view', $this->selectedDocument);
        $this->showDocumentModal = true;
        $this->reviewNotes = '';
        $this->reviewAction = '';
    }

    public function approveDocument()
    {
        if (!$this->selectedDocument) return;

        $this->authorize('update', $this->selectedDocument);

        // Check MFA for sensitive operations
        $mfaService = app(MFAService::class);
        $admin = auth('admin')->user();

        if (in_array($admin->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $mfaService->isMFAEnabled($admin)) {

            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                session()->flash('error', 'MFA verification required for this operation.');
                return;
            }
        }

        $reviewService = app(ReviewManagementService::class);

        // Start review if not already started
        if (!$this->selectedDocument->review_started_at) {
            $reviewService->startReview($this->selectedDocument, $admin);
        }

        $this->selectedDocument->update([
            'status' => 'approved',
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes,
        ]);

        $reviewService->completeReview($this->selectedDocument, $admin, 'approved');

        // Log audit
        $auditService = app(AuditLoggingService::class);
        $auditService->logDocumentReview($admin, $this->selectedDocument->id, 'approved', [
            'candidate_id' => $this->selectedDocument->candidate_id,
            'document_type' => $this->selectedDocument->document_type,
            'review_notes' => $this->reviewNotes,
        ]);

        session()->flash('success', 'Document approved successfully.');
        $this->closeDocumentModal();
    }

    public function rejectDocument()
    {
        if (!$this->selectedDocument) return;

        $this->authorize('update', $this->selectedDocument);

        if (empty($this->reviewNotes)) {
            session()->flash('error', 'Please provide a reason for rejection.');
            return;
        }

        // Check MFA for sensitive operations
        $mfaService = app(MFAService::class);
        $admin = auth('admin')->user();

        if (in_array($admin->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $mfaService->isMFAEnabled($admin)) {

            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                session()->flash('error', 'MFA verification required for this operation.');
                return;
            }
        }

        $reviewService = app(ReviewManagementService::class);

        // Start review if not already started
        if (!$this->selectedDocument->review_started_at) {
            $reviewService->startReview($this->selectedDocument, $admin);
        }

        $this->selectedDocument->update([
            'status' => 'rejected',
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes,
        ]);

        $reviewService->completeReview($this->selectedDocument, $admin, 'rejected', $this->reviewNotes);

        // Log audit
        $auditService = app(AuditLoggingService::class);
        $auditService->logDocumentReview($admin, $this->selectedDocument->id, 'rejected', [
            'candidate_id' => $this->selectedDocument->candidate_id,
            'document_type' => $this->selectedDocument->document_type,
            'review_notes' => $this->reviewNotes,
        ]);

        session()->flash('success', 'Document rejected.');
        $this->closeDocumentModal();
    }

    public function downloadDocument()
    {
        if (!$this->selectedDocument) return;

        $this->authorize('view', $this->selectedDocument);

        if (!Storage::disk('public')->exists($this->selectedDocument->filename)) {
            session()->flash('error', 'Document file not found.');
            return response()->json(['error' => 'File not found'], 404);
        }

        return response()->download(
            Storage::disk('public')->path($this->selectedDocument->filename),
            $this->selectedDocument->original_filename
        );
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedDocuments = $this->getDocumentsQuery()
                ->where('status', 'pending')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedDocuments = [];
        }
    }

    public function bulkApprove()
    {
        $this->authorize('update', CandidateDocument::class);

        if (empty($this->selectedDocuments)) {
            session()->flash('error', 'No documents selected.');
            return;
        }

        // Check MFA for sensitive operations
        $mfaService = app(MFAService::class);
        $admin = auth('admin')->user();

        if (in_array($admin->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $mfaService->isMFAEnabled($admin)) {

            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                session()->flash('error', 'MFA verification required for this operation.');
                return;
            }
        }

        $documents = CandidateDocument::whereIn('id', $this->selectedDocuments)->get();

        $documentService = app(DocumentManagementService::class);
        $count = $documentService->bulkApprove($documents, $admin->id);

        // Log audit
        $auditService = app(AuditLoggingService::class);
        $auditService->logAdminAction($admin, 'bulk_document_approval', [
            'documents_approved' => $count,
            'document_ids' => $this->selectedDocuments,
        ]);

        $this->selectedDocuments = [];
        $this->selectAll = false;

        session()->flash('success', "Approved {$count} documents.");
    }

    public function bulkReject()
    {
        $this->authorize('update', CandidateDocument::class);

        if (empty($this->selectedDocuments)) {
            session()->flash('error', 'No documents selected.');
            return;
        }

        if (empty($this->bulkRejectionReason)) {
            session()->flash('error', 'Please provide a reason for bulk rejection.');
            return;
        }

        // Check MFA for sensitive operations
        $mfaService = app(MFAService::class);
        $admin = auth('admin')->user();

        if (in_array($admin->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $mfaService->isMFAEnabled($admin)) {

            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                session()->flash('error', 'MFA verification required for this operation.');
                return;
            }
        }

        $documents = CandidateDocument::whereIn('id', $this->selectedDocuments)->get();

        $documentService = app(DocumentManagementService::class);
        $count = $documentService->bulkReject($documents, $admin->id, $this->bulkRejectionReason);

        // Log audit
        $auditService = app(AuditLoggingService::class);
        $auditService->logAdminAction($admin, 'bulk_document_rejection', [
            'documents_rejected' => $count,
            'document_ids' => $this->selectedDocuments,
            'reason' => $this->bulkRejectionReason,
        ]);

        $this->selectedDocuments = [];
        $this->selectAll = false;
        $this->bulkRejectionReason = '';

        session()->flash('success', "Rejected {$count} documents.");
    }

    public function closeDocumentModal()
    {
        $this->showDocumentModal = false;
        $this->selectedDocument = null;
        $this->reviewNotes = '';
        $this->reviewAction = '';
    }

    public function openAssignModal($documentId)
    {
        $this->assignDocumentId = $documentId;
        $reviewService = app(ReviewManagementService::class);
        $this->availableReviewers = $reviewService->getAvailableReviewers()->map(function ($reviewer) {
            return [
                'id' => $reviewer->id,
                'name' => $reviewer->full_name,
                'workload' => $reviewer->current_workload,
            ];
        })->toArray();
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assignDocumentId = null;
        $this->selectedReviewerId = null;
        $this->availableReviewers = [];
    }

    public function assignReviewer()
    {
        $this->validate([
            'selectedReviewerId' => 'required|exists:admins,id',
        ]);

        $document = CandidateDocument::findOrFail($this->assignDocumentId);
        $reviewer = \App\Models\Admin::findOrFail($this->selectedReviewerId);

        $reviewService = app(ReviewManagementService::class);
        if ($reviewService->assignReviewer($document, $reviewer)) {
            session()->flash('success', 'Reviewer assigned successfully.');
            $this->closeAssignModal();
        } else {
            session()->flash('error', 'Failed to assign reviewer.');
        }
    }

    public function autoAssignReviewers()
    {
        $reviewService = app(ReviewManagementService::class);
        $stats = $reviewService->autoAssignReviewers();

        if ($stats['candidate_documents_assigned'] > 0) {
            session()->flash('success', "Auto-assigned {$stats['candidate_documents_assigned']} documents.");
        } else {
            session()->flash('info', 'No documents available for auto-assignment.');
        }
    }

    private function getDocumentsQuery()
    {
        return CandidateDocument::with(['candidate.user', 'candidate.election', 'candidate.position', 'assignedReviewer'])
            ->when($this->search, function ($query) {
                $query->whereHas('candidate.user', function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%");
                })->orWhereHas('candidate.election', function ($q) {
                    $q->where('title', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->documentTypeFilter !== 'all', fn($q) => $q->where('document_type', $this->documentTypeFilter))
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        \Illuminate\Support\Facades\Log::info('DocumentReview::render called');

        try {
            $documents = $this->getDocumentsQuery()->paginate(20);

            $stats = [
                'total' => CandidateDocument::count(),
                'pending' => CandidateDocument::where('status', 'pending')->count(),
                'approved' => CandidateDocument::where('status', 'approved')->count(),
                'rejected' => CandidateDocument::where('status', 'rejected')->count(),
            ];

            \Illuminate\Support\Facades\Log::info('DocumentReview::render - data loaded successfully', [
                'documents_count' => $documents->count(),
                'stats' => $stats
            ]);

            return view('livewire.admin.document-review', compact('documents', 'stats'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DocumentReview::render - error loading data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}