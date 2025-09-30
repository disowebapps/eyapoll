<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\IdDocument;
use App\Enums\Auth\DocumentType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KycUpload extends Component
{
    use WithFileUploads;

    public $documentType = '';
    public $uploadedFile;
    public $documents = [];
    public $showUploadModal = false;
    public $uploading = false;
    public $uploadProgress = 0;

    protected function rules()
    {
        $documentTypes = implode(',', array_column(DocumentType::cases(), 'value'));
        return [
            'documentType' => "required|in:{$documentTypes}",
            'uploadedFile' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240|min:10',
        ];
    }

    protected $messages = [
        'documentType.required' => 'Please select the type of document you are uploading.',
        'documentType.in' => 'Please select a valid document type.',
        'uploadedFile.required' => 'Please select a file to upload.',
        'uploadedFile.file' => 'The uploaded item must be a valid file.',
        'uploadedFile.mimes' => 'Only JPG, PNG, and PDF files are accepted. Please check your file format.',
        'uploadedFile.max' => 'File size must not exceed 10MB. Please compress or resize your file.',
        'uploadedFile.min' => 'The uploaded file appears to be empty or corrupted.',
    ];

    public function mount()
    {
        $user = Auth::user();
        Log::info('KYC upload component mounted', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_status' => $user->status,
        ]);

        $this->loadDocuments();
    }

    public function loadDocuments()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        Log::debug('Loading KYC documents', [
            'user_id' => $user->id,
        ]);

        $this->documents = $user->idDocuments()
            ->with('reviewer')
            ->orderBy('created_at', 'desc')
            ->get();

        Log::debug('KYC documents loaded', [
            'user_id' => $user->id,
            'document_count' => count($this->documents),
            'document_ids' => collect($this->documents)->pluck('id')->toArray(),
        ]);
    }

    public function openUploadModal()
    {
        $user = Auth::user();

        // Check if user has exceeded resubmission limit
        if ($user->hasExceededResubmissionLimit()) {
            Log::warning('KYC upload blocked: resubmission limit exceeded', [
                'user_id' => $user->id,
                'rejection_count' => $user->rejection_count,
                'max_resubmissions' => config('ayapoll.kyc.max_resubmissions', 3),
            ]);
            session()->flash('error', 'You have exceeded the maximum number of document resubmissions. Please contact support for assistance.');
            return;
        }

        Log::info('KYC upload modal opened', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_status' => $user->status,
            'rejection_count' => $user->rejection_count,
            'remaining_attempts' => $user->getRemainingResubmissionAttempts(),
        ]);

        $this->showUploadModal = true;
        $this->resetForm();
    }

    public function closeUploadModal()
    {
        $user = Auth::user();
        Log::info('KYC upload modal closed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'had_file' => !is_null($this->uploadedFile),
            'document_type_selected' => $this->documentType,
            'has_errors' => $this->getErrorBag()->any(),
        ]);

        $this->showUploadModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $user = Auth::user();
        Log::debug('KYC upload form reset', [
            'user_id' => $user->id,
            'previous_document_type' => $this->documentType,
            'had_file' => !is_null($this->uploadedFile),
        ]);

        $this->documentType = '';
        $this->uploadedFile = null;
        $this->resetErrorBag();
    }

    /**
     * Verify file content type by reading actual file content
     */
    private function verifyFileContentType(): void
    {
        if (!$this->uploadedFile) {
            return;
        }

        // Get actual MIME type from file content
        $actualMimeType = $this->getActualMimeType($this->uploadedFile->getRealPath());

        // Define allowed MIME types
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf'
        ];

        if (!in_array($actualMimeType, $allowedMimeTypes)) {
            Log::warning('KYC upload blocked: invalid content type', [
                'user_id' => Auth::id(),
                'client_mime' => $this->uploadedFile->getMimeType(),
                'actual_mime' => $actualMimeType,
                'file_name' => $this->uploadedFile->getClientOriginalName(),
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'uploadedFile' => ['The uploaded file content does not match the expected format. Only JPG, PNG, and PDF files are allowed.']
            ]);
        }

        // Additional security check: verify file extension matches content
        $extension = strtolower($this->uploadedFile->getClientOriginalExtension());
        $expectedExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'application/pdf' => ['pdf']
        ];

        if (isset($expectedExtensions[$actualMimeType]) && !in_array($extension, $expectedExtensions[$actualMimeType])) {
            Log::warning('KYC upload blocked: extension mismatch', [
                'user_id' => Auth::id(),
                'actual_mime' => $actualMimeType,
                'extension' => $extension,
                'expected_extensions' => $expectedExtensions[$actualMimeType],
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'uploadedFile' => ['File extension does not match the file content type.']
            ]);
        }
    }

    /**
     * Get actual MIME type from file content
     */
    private function getActualMimeType(string $filePath): string
    {
        // Use finfo for accurate MIME type detection
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        }

        // Fallback to mime_content_type if finfo is not available
        return mime_content_type($filePath);
    }

    public function uploadDocument()
    {
        $user = Auth::user();

        Log::info('KYC upload initiated', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_status' => $user->status,
            'document_type' => $this->documentType,
            'has_file' => !is_null($this->uploadedFile),
            'file_name' => $this->uploadedFile ? $this->uploadedFile->getClientOriginalName() : null,
            'file_size' => $this->uploadedFile ? $this->uploadedFile->getSize() : null,
            'file_mime' => $this->uploadedFile ? $this->uploadedFile->getMimeType() : null,
        ]);

        // Check if user can upload documents (basic check)
        if (!in_array($user->status, [\App\Enums\Auth\UserStatus::PENDING, \App\Enums\Auth\UserStatus::REJECTED])) {
            Log::warning('KYC upload blocked: invalid user status', [
                'user_id' => $user->id,
                'user_status' => $user->status->value,
                'user_status_enum' => $user->status,
            ]);
            session()->flash('error', 'Document upload not available. Please complete your registration first.');
            return;
        }

        // Check resubmission limit
        if ($user->hasExceededResubmissionLimit()) {
            Log::warning('KYC upload blocked: resubmission limit exceeded', [
                'user_id' => $user->id,
                'rejection_count' => $user->rejection_count,
                'max_resubmissions' => config('ayapoll.kyc.max_resubmissions', 3),
            ]);
            session()->flash('error', 'You have exceeded the maximum number of document resubmissions. Please contact support for assistance.');
            return;
        }

        Log::info('KYC upload: user status check passed', [
            'user_id' => $user->id,
            'user_status' => $user->status,
        ]);

        try {
            Log::info('KYC upload: starting validation', [
                'user_id' => $user->id,
                'document_type' => $this->documentType,
            ]);

            $this->validate();

            // Server-side content-type verification
            $this->verifyFileContentType();

            Log::info('KYC upload: validation passed', [
                'user_id' => $user->id,
                'document_type' => $this->documentType,
            ]);

            $this->uploading = true;

            Log::info('KYC upload: starting KYC service upload', [
                'user_id' => $user->id,
                'document_type' => $this->documentType,
            ]);

            $kycService = app(\App\Services\Verification\KycService::class);
            $document = $kycService->uploadDocument($user, [
                'type' => $this->documentType,
                'file' => $this->uploadedFile,
            ]);

            Log::info('KYC upload: KYC service upload completed', [
                'user_id' => $user->id,
                'document_id' => $document->id ?? null,
                'document_type' => $this->documentType,
            ]);

            // Perform quick verification (non-blocking)
            Log::info('KYC upload: starting quick verification', [
                'user_id' => $user->id,
                'document_id' => $document->id ?? null,
            ]);

            $verificationService = app(\App\Services\Document\DocumentVerificationService::class);
            $verificationResult = $verificationService->quickVerify($document);

            Log::info('KYC upload: quick verification completed', [
                'user_id' => $user->id,
                'document_id' => $document->id ?? null,
                'verification_status' => $verificationResult['status'] ?? null,
                'verification_errors' => $verificationResult['errors'] ?? null,
            ]);

            // Dispatch background job for full verification
            Log::info('KYC upload: dispatching background verification job', [
                'user_id' => $user->id,
                'document_id' => $document->id ?? null,
            ]);

            \App\Jobs\ProcessDocumentVerification::dispatch($document)->onQueue('document-verification');

            $message = 'Document uploaded successfully.';
            if ($verificationResult['status'] === 'failed') {
                $message .= ' However, some verification checks failed. Please ensure your document is authentic and try again if needed.';
            } else {
                $message .= ' Basic verification passed. Full verification is in progress and it will be reviewed by our administrators.';
            }

            Log::info('KYC upload: upload process completed successfully', [
                'user_id' => $user->id,
                'document_id' => $document->id ?? null,
                'verification_status' => $verificationResult['status'] ?? null,
                'message' => $message,
            ]);

            session()->flash('success', $message);
            $this->closeUploadModal();
            $this->loadDocuments();
            
            // Dispatch browser event to ensure modal closes
            $this->dispatch('upload-success');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('KYC upload validation failed', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
                'document_type' => $this->documentType,
            ]);
            throw $e; // Re-throw to let Livewire handle validation errors

        } catch (\InvalidArgumentException $e) {
            Log::warning('KYC upload invalid argument', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'document_type' => $this->documentType,
            ]);
            $this->addError('upload', $e->getMessage());

        } catch (\Exception $e) {
            Log::error('KYC upload failed with exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'document_type' => $this->documentType,
                'file_name' => $this->uploadedFile ? $this->uploadedFile->getClientOriginalName() : null,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('upload', 'Upload failed. Please try again or contact support.');
        }

        $this->uploading = false;

        Log::info('KYC upload method completed', [
            'user_id' => $user->id,
            'uploading_flag' => $this->uploading,
            'has_errors' => $this->getErrorBag()->any(),
        ]);
    }

    public function render()
    {
        return view('livewire.voter.kyc-upload', [
            'documentTypes' => DocumentType::cases()
        ]);
    }
}