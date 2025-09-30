<?php

namespace App\Services;

use App\Models\Auth\IdDocument;
use App\Models\Candidate\CandidateDocument;
use App\Enums\Auth\DocumentType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentManagementService
{
    /**
     * Create a new version of a document
     */
    public function createDocumentVersion(IdDocument|CandidateDocument $document, array $attributes = []): IdDocument|CandidateDocument
    {
        $modelClass = get_class($document);

        // Get the latest version number for this document type and owner
        $latestVersion = $modelClass::where('user_id', $document->user_id ?? $document->candidate_id)
            ->where('document_type', $document->document_type)
            ->max('version_number') ?? 0;

        $newVersion = $latestVersion + 1;

        $versionAttributes = array_merge($attributes, [
            'version_number' => $newVersion,
            'parent_id' => $document->id,
            'status' => 'pending', // New versions start as pending
        ]);

        // Copy relevant attributes from parent
        $versionAttributes = array_merge([
            'user_id' => $document->user_id ?? null,
            'candidate_id' => $document->candidate_id ?? null,
            'document_type' => $document->document_type,
            'file_path' => $document->file_path,
            'file_hash' => $document->file_hash,
            'expiry_date' => $document->expiry_date,
            'country_code' => $document->country_code,
        ], $versionAttributes);

        return $modelClass::create($versionAttributes);
    }

    /**
     * Bulk approve documents
     */
    public function bulkApprove(Collection $documents, int $reviewerId): int
    {
        $count = 0;

        DB::transaction(function () use ($documents, $reviewerId, &$count) {
            foreach ($documents as $document) {
                if ($document->canBeApproved()) {
                    $document->update([
                        'status' => 'approved',
                        'reviewed_by' => $reviewerId,
                        'reviewed_at' => now(),
                    ]);
                    $count++;

                    Log::info("Document {$document->id} approved by user {$reviewerId}");
                }
            }
        });

        return $count;
    }

    /**
     * Bulk reject documents
     */
    public function bulkReject(Collection $documents, int $reviewerId, string $reason): int
    {
        $count = 0;

        DB::transaction(function () use ($documents, $reviewerId, $reason, &$count) {
            foreach ($documents as $document) {
                if ($document->canBeRejected()) {
                    $document->update([
                        'status' => 'rejected',
                        'rejection_reason' => $reason,
                        'reviewed_by' => $reviewerId,
                        'reviewed_at' => now(),
                    ]);
                    $count++;

                    Log::info("Document {$document->id} rejected by user {$reviewerId}: {$reason}");
                }
            }
        });

        return $count;
    }

    /**
     * Get documents expiring soon
     */
    public function getExpiringDocuments(int $days = 30): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "expiring_documents_{$days}",
            3600, // 1 hour
            function () use ($days) {
                $idDocuments = IdDocument::expiringSoon($days)->get();
                $candidateDocuments = CandidateDocument::expiringSoon($days)->get();
                return $idDocuments->merge($candidateDocuments);
            }
        );
    }

    /**
     * Get expired documents
     */
    public function getExpiredDocuments(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember(
            "expired_documents",
            3600, // 1 hour
            function () {
                $idDocuments = IdDocument::expired()->get();
                $candidateDocuments = CandidateDocument::expired()->get();
                return $idDocuments->merge($candidateDocuments);
            }
        );
    }

    /**
     * Bulk update document expiry dates
     */
    public function bulkUpdateExpiry(Collection $documents, string $expiryDate): int
    {
        $count = 0;

        DB::transaction(function () use ($documents, $expiryDate, &$count) {
            foreach ($documents as $document) {
                $document->update(['expiry_date' => $expiryDate]);
                $count++;

                Log::info("Document {$document->id} expiry updated to {$expiryDate}");
            }
        });

        return $count;
    }

    /**
     * Get document history/versions
     */
    public function getDocumentHistory(IdDocument|CandidateDocument $document): Collection
    {
        $modelClass = get_class($document);

        return $modelClass::where(function ($query) use ($document) {
            $query->where('id', $document->id)
                  ->orWhere('parent_id', $document->id);
        })
        ->orWhere(function ($query) use ($document) {
            $query->where('parent_id', $document->parent_id)
                  ->where('id', '!=', $document->id);
        })
        ->orderBy('version_number')
        ->get();
    }

    /**
     * Get latest versions of documents for a user/candidate
     */
    public function getLatestDocuments(int $ownerId, bool $isCandidate = false): Collection
    {
        if ($isCandidate) {
            return CandidateDocument::where('candidate_id', $ownerId)
                ->latestVersion()
                ->get();
        }

        return IdDocument::where('user_id', $ownerId)
            ->latestVersion()
            ->get();
    }

    /**
     * Validate document for international format
     */
    public function validateInternationalDocument(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        // Check if country code is valid (ISO 3166-1 alpha-3)
        if ($document->country_code && !preg_match('/^[A-Z]{3}$/', $document->country_code)) {
            $errors[] = 'Invalid country code format. Must be ISO 3166-1 alpha-3.';
        }

        // Document type specific validations
        if ($document instanceof IdDocument) {
            $errors = array_merge($errors, $this->validateIdDocumentType($document));
        }

        // International format validations
        $errors = array_merge($errors, $this->validateInternationalFormats($document));

        return $errors;
    }

    /**
     * Validate international document formats
     */
    private function validateInternationalFormats(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        switch ($document->document_type) {
            case 'visa':
                $errors = array_merge($errors, $this->validateVisaFormat($document));
                break;
            case 'residence_permit':
                $errors = array_merge($errors, $this->validateResidencePermitFormat($document));
                break;
            case 'international_passport':
                $errors = array_merge($errors, $this->validateInternationalPassportFormat($document));
                break;
            case 'birth_certificate':
                $errors = array_merge($errors, $this->validateBirthCertificateFormat($document));
                break;
            case 'marriage_certificate':
                $errors = array_merge($errors, $this->validateMarriageCertificateFormat($document));
                break;
        }

        return $errors;
    }

    /**
     * Validate visa format
     */
    private function validateVisaFormat(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        if (!$document->country_code) {
            $errors[] = 'Visa documents must specify the issuing country.';
        }

        if (!$document->expiry_date) {
            $errors[] = 'Visa documents must have an expiry date.';
        } elseif ($document->expiry_date->isPast()) {
            $errors[] = 'Visa has already expired.';
        }

        // Visa numbers typically follow specific patterns
        // This is a basic validation - could be enhanced with country-specific rules
        if (strlen($document->file_hash) < 10) {
            $errors[] = 'Visa document appears to be invalid or corrupted.';
        }

        return $errors;
    }

    /**
     * Validate residence permit format
     */
    private function validateResidencePermitFormat(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        if (!$document->country_code) {
            $errors[] = 'Residence permit documents must specify the issuing country.';
        }

        if (!$document->expiry_date) {
            $errors[] = 'Residence permit documents must have an expiry date.';
        }

        // Check if expiry is reasonable (not too far in future, not past)
        if ($document->expiry_date) {
            $now = now();
            $maxYears = 10; // Residence permits typically don't exceed 10 years

            if ($document->expiry_date->diffInYears($now) > $maxYears) {
                $errors[] = 'Residence permit expiry date seems unreasonable (more than 10 years).';
            }

            if ($document->expiry_date->isPast()) {
                $errors[] = 'Residence permit has expired.';
            }
        }

        return $errors;
    }

    /**
     * Validate international passport format
     */
    private function validateInternationalPassportFormat(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        if (!$document->country_code) {
            $errors[] = 'International passport documents must specify the issuing country.';
        }

        if (!$document->expiry_date) {
            $errors[] = 'International passport documents must have an expiry date.';
        } elseif ($document->expiry_date->isPast()) {
            $errors[] = 'International passport has expired.';
        }

        // International passports typically valid for 5-10 years
        if ($document->expiry_date) {
            $issueDate = $document->created_at ?? now();
            $validityYears = $document->expiry_date->diffInYears($issueDate);

            if ($validityYears < 1 || $validityYears > 15) {
                $errors[] = 'International passport validity period seems unusual (should be 1-15 years).';
            }
        }

        return $errors;
    }

    /**
     * Validate birth certificate format
     */
    private function validateBirthCertificateFormat(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        // Birth certificates don't typically expire, but some countries require them to be recent
        if ($document->expiry_date) {
            $errors[] = 'Birth certificates typically do not have expiry dates.';
        }

        if (!$document->country_code) {
            $errors[] = 'Birth certificate documents should specify the issuing country.';
        }

        return $errors;
    }

    /**
     * Validate marriage certificate format
     */
    private function validateMarriageCertificateFormat(IdDocument|CandidateDocument $document): array
    {
        $errors = [];

        // Marriage certificates don't typically expire
        if ($document->expiry_date) {
            $errors[] = 'Marriage certificates typically do not have expiry dates.';
        }

        if (!$document->country_code) {
            $errors[] = 'Marriage certificate documents should specify the issuing country.';
        }

        return $errors;
    }

    /**
     * Validate ID document type requirements
     */
    private function validateIdDocumentType(IdDocument $document): array
    {
        $errors = [];

        switch ($document->document_type) {
            case DocumentType::PASSPORT:
            case DocumentType::INTERNATIONAL_PASSPORT:
                if (!$document->expiry_date) {
                    $errors[] = 'Passport documents must have an expiry date';
                }
                break;

            case DocumentType::DRIVERS_LICENSE:
            case DocumentType::DRIVERS_LICENSE_PROVISIONAL:
            case DocumentType::DRIVERS_LICENSE_FULL:
                if (!$document->expiry_date) {
                    $errors[] = 'Driver\'s license documents must have an expiry date';
                }
                break;

            case DocumentType::VISA:
            case DocumentType::RESIDENCE_PERMIT:
                if (!$document->expiry_date) {
                    $errors[] = 'Visa and residence permit documents must have an expiry date';
                }
                if (!$document->country_code) {
                    $errors[] = 'Visa and residence permit documents must specify a country';
                }
                break;
        }

        return $errors;
    }

    /**
     * Send expiry notifications
     */
    public function sendExpiryNotifications(Collection $documents, int $daysUntilExpiry): void
    {
        foreach ($documents as $document) {
            // Implementation would depend on your notification system
            // This is a placeholder for the notification logic
            $this->sendExpiryNotification($document, $daysUntilExpiry);
        }
    }

    /**
     * Send single expiry notification
     */
    private function sendExpiryNotification(IdDocument|CandidateDocument $document, int $daysUntilExpiry): void
    {
        // Placeholder - implement actual notification sending
        Log::info("Expiry notification sent for document {$document->id}, expires in {$daysUntilExpiry} days");
    }
}