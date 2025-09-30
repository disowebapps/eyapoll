<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Database\Eloquent\Model;
use App\Services\DocumentVerificationService;
use Illuminate\Support\Facades\Log;

class ProcessDocumentVerification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $timeout = 300; // 5 minutes timeout

    protected Model $document;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentVerificationService $verificationService): void
    {
        try {
            Log::info('Starting document verification job', [
                'document_id' => $this->document->id,
                'document_type' => get_class($this->document),
            ]);

            $result = $verificationService->verifyDocument($this->document);

            Log::info('Document verification completed', [
                'document_id' => $this->document->id,
                'status' => $result['status'],
                'score' => $result['score'],
                'errors_count' => count($result['errors']),
            ]);

        } catch (\Exception $e) {
            Log::error('Document verification job failed', [
                'document_id' => $this->document->id,
                'document_type' => get_class($this->document),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Document verification job permanently failed', [
            'document_id' => $this->document->id,
            'document_type' => get_class($this->document),
            'error' => $exception->getMessage(),
        ]);

        // Mark document as failed verification
        $this->document->update([
            'verification_status' => 'failed',
            'verification_errors' => json_encode(['Verification process failed: ' . $exception->getMessage()]),
            'verified_at' => now(),
        ]);
    }
}
