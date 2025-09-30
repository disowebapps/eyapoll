<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Auth\IdDocument;
use App\Models\User;
use App\Livewire\Admin\BaseAdminComponent;
use App\Services\Auth\MFAService;
use App\Services\Monitoring\AuditLoggingService;
use App\Services\Document\DocumentManagementService;
use App\Services\Utility\ReviewManagementService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KycReview extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'pending';
    public $selectedDocument = null;
    public $showReviewModal = false;
    public $reviewAction = '';
    public $rejectionReason = '';
    public $reviewing = false;

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

    protected $queryString = ['search', 'statusFilter'];

    public function mount()
    {
        // Admin authentication is handled by BaseAdminComponent
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function viewDocument($documentId)
    {
        $document = IdDocument::with('user', 'assignedReviewer')->findOrFail($documentId);
        $this->selectedDocument = [
            'id' => $document->id,
            'user_name' => $document->user->first_name . ' ' . $document->user->last_name,
            'user_email' => $document->user->email,
            'document_type' => $document->document_type,
            'file_path' => $document->file_path,
            'status' => $document->status,
            'uploaded_at' => $document->created_at,
            'reviewed_at' => $document->reviewed_at,
            'rejection_reason' => $document->rejection_reason,
            'assigned_reviewer' => $document->assigned_reviewer?->full_name,
            'assigned_at' => $document->assigned_at,
            'escalated_at' => $document->escalated_at,
            'review_started_at' => $document->review_started_at,
            'review_completed_at' => $document->review_completed_at,
        ];
        $this->showReviewModal = true;
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

        $documents = IdDocument::whereIn('id', $this->selectedDocuments)->get();
        $kycService = app(\App\Services\Verification\KycService::class);
        $count = 0;

        DB::transaction(function () use ($documents, $admin, $kycService, &$count) {
            foreach ($documents as $document) {
                try {
                    $kycService->approveUser($document->user, $admin);
                    $count++;

                    // Log audit
                    $auditService = app(\App\Services\Monitoring\AuditLoggingService::class);
                    $auditService->logKycReview($admin, $document->user, 'approve', [
                        'document_id' => $document->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Bulk KYC approval failed', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $this->selectedDocuments = [];
        $this->selectAll = false;

        session()->flash('success', "Approved {$count} users.");
    }

    public function bulkReject()
    {
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

        $documents = IdDocument::whereIn('id', $this->selectedDocuments)->get();
        $kycService = app(\App\Services\Verification\KycService::class);
        $count = 0;

        DB::transaction(function () use ($documents, $admin, $kycService, &$count) {
            foreach ($documents as $document) {
                try {
                    $kycService->rejectUser($document->user, $admin, $this->bulkRejectionReason);
                    $count++;

                    // Log audit
                    $auditService = app(\App\Services\Monitoring\AuditLoggingService::class);
                    $auditService->logKycReview($admin, $document->user, 'reject', [
                        'document_id' => $document->id,
                        'rejection_reason' => $this->bulkRejectionReason,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Bulk KYC rejection failed', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });

        $this->selectedDocuments = [];
        $this->selectAll = false;
        $this->bulkRejectionReason = '';

        session()->flash('success', "Rejected {$count} users.");
    }

    public function closeReviewModal()
    {
        $this->showReviewModal = false;
        $this->selectedDocument = null;
        $this->reviewAction = '';
        $this->rejectionReason = '';
        $this->resetErrorBag();
    }

    public function openAssignModal($documentId)
    {
        $this->assignDocumentId = $documentId;
        $reviewService = app(\App\Services\Utility\ReviewManagementService::class);
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

        $document = IdDocument::findOrFail($this->assignDocumentId);
        $reviewer = \App\Models\Admin::findOrFail($this->selectedReviewerId);

        $reviewService = app(\App\Services\Utility\ReviewManagementService::class);
        if ($reviewService->assignReviewer($document, $reviewer)) {
            session()->flash('success', 'Reviewer assigned successfully.');
            $this->closeAssignModal();
        } else {
            session()->flash('error', 'Failed to assign reviewer.');
        }
    }

    public function autoAssignReviewers()
    {
        $reviewService = app(\App\Services\Utility\ReviewManagementService::class);
        $stats = $reviewService->autoAssignReviewers();

        if ($stats['id_documents_assigned'] > 0) {
            session()->flash('success', "Auto-assigned {$stats['id_documents_assigned']} documents.");
        } else {
            session()->flash('info', 'No documents available for auto-assignment.');
        }
    }

    public function approveDocument()
    {
        $this->reviewAction = 'approve';
        $this->validateRejectionReason();
        $this->processReview();
    }

    public function rejectDocument()
    {
        $this->reviewAction = 'reject';
        $this->validate([
            'rejectionReason' => 'required|string|max:500'
        ]);
        $this->processReview();
    }

    private function validateRejectionReason()
    {
        if ($this->reviewAction === 'reject') {
            $this->validate([
                'rejectionReason' => 'required|string|max:500'
            ]);
        }
    }

    private function processReview()
    {
        $this->reviewing = true;

        // Check MFA for sensitive operations
        $mfaService = app(MFAService::class);
        $admin = auth('admin')->user();

        if (in_array($admin->role, \App\Enums\Auth\UserRole::getAdminRoles()) &&
            $mfaService->isMFAEnabled($admin)) {

            $lastMfaVerification = session('mfa_verified_at');
            if (!$lastMfaVerification || now()->diffInMinutes($lastMfaVerification) > 30) {
                session()->flash('error', 'MFA verification required for this operation.');
                $this->reviewing = false;
                return;
            }
        }

        DB::transaction(function () use ($mfaService, $admin) {
            try {
                $document = IdDocument::findOrFail($this->selectedDocument['id']);
                $user = $document->user;

                $kycService = app(\App\Services\Verification\KycService::class);
                $reviewService = app(\App\Services\Utility\ReviewManagementService::class);

                // Start review if not already started
                if (!$document->review_started_at) {
                    $reviewService->startReview($document, $admin);
                }

                if ($this->reviewAction === 'approve') {
                    $kycService->approveUser($user, $admin);
                    $reviewService->completeReview($document, $admin, 'approved');
                    session()->flash('success', 'User approved successfully.');
                } elseif ($this->reviewAction === 'reject') {
                    $kycService->rejectUser($user, $admin, $this->rejectionReason);
                    $reviewService->completeReview($document, $admin, 'rejected', $this->rejectionReason);
                    session()->flash('success', 'User rejected.');
                }

                // Log audit
                $auditService = app(\App\Services\Monitoring\AuditLoggingService::class);
                $auditService->logKycReview($admin, $user, $this->reviewAction, [
                    'document_id' => $document->id,
                    'rejection_reason' => $this->rejectionReason ?? null,
                ]);

                $this->closeReviewModal();

            } catch (\Exception $e) {
                Log::error('KYC review failed', [
                    'document_id' => $this->selectedDocument['id'] ?? null,
                    'admin_id' => auth('admin')->id(),
                    'error' => $e->getMessage()
                ]);
                session()->flash('error', 'Review failed. Please try again.');
            }
        });

        $this->reviewing = false;
    }

    public function getDocumentTypeLabel($type)
    {
        if ($type instanceof \App\Enums\Auth\DocumentType) {
            return $type->label();
        }
        
        return match($type) {
            'national_id' => 'National ID',
            'passport' => 'Passport',
            'drivers_license' => 'Driver\'s License',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }

    public function getStatusColor($status)
    {
        return match($status) {
            'approved' => 'green',
            'rejected' => 'red',
            'pending' => 'yellow',
            default => 'gray'
        };
    }

    private function getDocumentsQuery()
    {
        return IdDocument::with('user', 'assignedReviewer')
            ->when($this->search, function($query) {
                $query->whereHas('user', function($q) {
                    $q->where('email', 'like', "%{$this->search}%")
                      ->orWhere('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter === 'pending', function($q) {
                // Show documents from users in REVIEW status
                $q->where('status', 'pending')
                  ->whereHas('user', fn($query) => $query->where('status', 'review'));
            })
            ->when($this->statusFilter !== 'all' && $this->statusFilter !== 'pending',
                fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $documents = $this->getDocumentsQuery()->paginate(15);

        return view('livewire.admin.kyc-review', [
            'documents' => $documents
        ]);
    }
}