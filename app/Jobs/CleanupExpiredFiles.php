<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\IdDocument;
use App\Models\Candidate\CandidateDocument;

class CleanupExpiredFiles implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->cleanupExpiredDocuments();
        $this->cleanupTempFiles();
        $this->cleanupOrphanedFiles();
    }

    /**
     * Clean up files from expired documents
     */
    private function cleanupExpiredDocuments(): void
    {
        $expiredIdDocuments = IdDocument::expired()->get();
        $expiredCandidateDocuments = CandidateDocument::expired()->get();

        $totalCleaned = 0;

        // Clean expired ID documents
        foreach ($expiredIdDocuments as $document) {
            if ($this->deleteDocumentFile($document)) {
                $totalCleaned++;
                Log::info("Cleaned expired ID document file: {$document->id}");
            }
        }

        // Clean expired candidate documents
        foreach ($expiredCandidateDocuments as $document) {
            if ($this->deleteDocumentFile($document)) {
                $totalCleaned++;
                Log::info("Cleaned expired candidate document file: {$document->id}");
            }
        }

        Log::info("Expired document cleanup completed", [
            'id_documents_cleaned' => $expiredIdDocuments->count(),
            'candidate_documents_cleaned' => $expiredCandidateDocuments->count(),
            'total_files_cleaned' => $totalCleaned
        ]);
    }

    /**
     * Clean up temporary files older than 24 hours
     */
    private function cleanupTempFiles(): void
    {
        $tempDirectories = ['livewire-tmp'];
        $cleanedCount = 0;

        foreach ($tempDirectories as $dir) {
            if (Storage::disk('private')->exists($dir)) {
                $files = Storage::disk('private')->files($dir);

                foreach ($files as $file) {
                    $filePath = Storage::disk('private')->path($file);
                    $fileModifiedTime = filemtime($filePath);

                    // Delete files older than 24 hours
                    if (time() - $fileModifiedTime > 86400) {
                        Storage::disk('private')->delete($file);
                        $cleanedCount++;
                        Log::info("Cleaned temp file: {$file}");
                    }
                }
            }
        }

        Log::info("Temp file cleanup completed", ['files_cleaned' => $cleanedCount]);
    }

    /**
     * Clean up orphaned files (files without database records)
     */
    private function cleanupOrphanedFiles(): void
    {
        $this->cleanupOrphanedFromDirectory('kyc-documents');
        $this->cleanupOrphanedFromDirectory('candidate-documents');
    }

    /**
     * Clean orphaned files from a specific directory
     */
    private function cleanupOrphanedFromDirectory(string $directory): void
    {
        if (!Storage::disk('private')->exists($directory)) {
            return;
        }

        $files = Storage::disk('private')->allFiles($directory);
        $cleanedCount = 0;

        foreach ($files as $file) {
            $filename = basename($file);

            // Check if file exists in database
            $existsInDb = $this->fileExistsInDatabase($filename, $directory);

            if (!$existsInDb) {
                Storage::disk('private')->delete($file);
                $cleanedCount++;
                Log::info("Cleaned orphaned file: {$file}");
            }
        }

        Log::info("Orphaned file cleanup completed for {$directory}", [
            'files_cleaned' => $cleanedCount
        ]);
    }

    /**
     * Check if a file exists in the database
     */
    private function fileExistsInDatabase(string $filename, string $directory): bool
    {
        // Extract file hash from filename (assuming format: type_hash.ext)
        $parts = explode('_', pathinfo($filename, PATHINFO_FILENAME));
        if (count($parts) < 2) {
            return false;
        }

        $fileHash = end($parts);

        if ($directory === 'kyc-documents') {
            return IdDocument::where('file_hash', $fileHash)->exists();
        } elseif ($directory === 'candidate-documents') {
            return CandidateDocument::where('file_hash', $fileHash)->exists();
        }

        return false;
    }

    /**
     * Delete a document's file
     */
    private function deleteDocumentFile($document): bool
    {
        try {
            $filePath = decrypt($document->file_path);
            $secureFileService = app(\App\Services\Security\SecureFileService::class);

            if ($secureFileService->exists($filePath)) {
                $secureFileService->delete($filePath);
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete document file: {$document->id}", [
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }
}
