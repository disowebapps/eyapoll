<?php

namespace App\Services\Candidate;

use App\Models\Candidate\Candidate;
use App\Models\Candidate\CandidateDocument;
use App\Models\Admin;
use App\Services\Audit\AuditLogService;
use App\Services\Security\FilePathValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CandidateDocumentService
{
    public function __construct(
        private AuditLogService $auditLog,
        private FilePathValidator $pathValidator
    ) {}

    public function uploadDocument(Candidate $candidate, UploadedFile $file, string $documentType): CandidateDocument
    {
        if (!$candidate->canUploadDocuments()) {
            throw new \InvalidArgumentException('Cannot upload documents for this candidate');
        }

        return DB::transaction(function () use ($candidate, $file, $documentType) {
            // Store file securely
            $directory = "candidate-documents/{$candidate->uuid}";
            $filename = $documentType . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($directory, $filename, 'local');
            
            if (!$this->pathValidator->validatePath(storage_path('app/' . $path))) {
                Storage::delete($path);
                throw new \InvalidArgumentException('Invalid file path');
            }
            $fileHash = hash_file('sha256', $file->path());

            $document = CandidateDocument::create([
                'candidate_id' => $candidate->id,
                'document_type' => $documentType,
                'file_path' => $path,
                'file_hash' => $fileHash,
                'status' => 'pending',
            ]);

            $this->auditLog->log(
                'candidate_document_uploaded',
                $candidate->user,
                CandidateDocument::class,
                $document->id,
                null,
                ['document_type' => $documentType]
            );

            return $document;
        });
    }

    public function approveDocument(CandidateDocument $document, Admin $admin): bool
    {
        if (!$document->canBeApproved()) {
            throw new \InvalidArgumentException('Document cannot be approved');
        }

        return DB::transaction(function () use ($document, $admin) {
            $success = $document->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_document_approved',
                    $admin,
                    CandidateDocument::class,
                    $document->id,
                    ['status' => 'pending'],
                    ['status' => 'approved']
                );
            }

            return $success;
        });
    }

    public function rejectDocument(CandidateDocument $document, Admin $admin, string $reason): bool
    {
        if (!$document->canBeRejected()) {
            throw new \InvalidArgumentException('Document cannot be rejected');
        }

        return DB::transaction(function () use ($document, $admin, $reason) {
            $success = $document->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ]);

            if ($success) {
                $this->auditLog->log(
                    'candidate_document_rejected',
                    $admin,
                    CandidateDocument::class,
                    $document->id,
                    ['status' => 'pending'],
                    ['status' => 'rejected', 'reason' => $reason]
                );
            }

            return $success;
        });
    }
}