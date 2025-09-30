<?php

namespace App\Services\Document;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use thiagoalessio\TesseractOCR\TesseractOCR;
use App\Models\Auth\IdDocument;
use App\Models\Candidate\CandidateDocument;
use Illuminate\Database\Eloquent\Model;

class DocumentVerificationService
{
    private ImageManager $imageManager;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Verify a document and update its verification fields
     */
    public function verifyDocument(Model $document): array
    {
        $filePath = decrypt($document->file_path);
        $fullPath = Storage::disk('private')->path($filePath);

        if (!file_exists($fullPath)) {
            return $this->failVerification($document, ['File not found']);
        }

        $errors = [];
        $authenticityScore = 100.0;
        $ocrText = null;
        $perceptualHash = null;

        try {
            // Hash verification for tampering detection
            $currentHash = hash_file('sha256', $fullPath);
            if ($document->file_hash !== $currentHash) {
                $errors[] = 'File hash mismatch - document may have been tampered with';
                $authenticityScore -= 50;
            }

            // Check if file is an image
            $mimeType = mime_content_type($fullPath);
            $isImage = str_starts_with($mimeType, 'image/');

            if ($isImage) {
                // Image authenticity verification
                $imageAuthenticity = $this->verifyImageAuthenticity($fullPath);
                if (!$imageAuthenticity['authentic']) {
                    $errors = array_merge($errors, $imageAuthenticity['errors']);
                    $authenticityScore = min($authenticityScore, $imageAuthenticity['score']);
                }

                // Generate perceptual hash for duplicate detection
                $perceptualHash = $this->generatePerceptualHash($fullPath);

                // OCR extraction
                $ocrText = $this->extractText($fullPath);
            }

            // Check for duplicates using perceptual hash
            if ($perceptualHash) {
                $duplicateCheck = $this->checkForDuplicates($document, $perceptualHash);
                if ($duplicateCheck['has_duplicates']) {
                    $errors[] = 'Potential duplicate document detected';
                    $authenticityScore -= 30;
                }
            }

        } catch (\Exception $e) {
            Log::error('Document verification failed', [
                'document_id' => $document->id,
                'document_type' => get_class($document),
                'error' => $e->getMessage()
            ]);
            $errors[] = 'Verification process failed: ' . $e->getMessage();
            $authenticityScore = 0;
        }

        // Determine verification status
        $status = empty($errors) ? 'passed' : 'failed';

        // Update document with verification results
        $document->update([
            'perceptual_hash' => $perceptualHash,
            'ocr_text' => $ocrText,
            'authenticity_score' => $authenticityScore,
            'verification_status' => $status,
            'verification_errors' => $errors ? json_encode($errors) : null,
            'verified_at' => now(),
        ]);

        return [
            'status' => $status,
            'score' => $authenticityScore,
            'errors' => $errors,
            'ocr_text' => $ocrText,
            'perceptual_hash' => $perceptualHash,
        ];
    }

    /**
     * Verify image authenticity using metadata and basic manipulation checks
     */
    private function verifyImageAuthenticity(string $filePath): array
    {
        $errors = [];
        $score = 100.0;

        try {
            $image = $this->imageManager->read($filePath);

            // Check EXIF data for manipulation indicators
            $exif = @exif_read_data($filePath);
            if ($exif) {
                // Check for software that might indicate editing
                if (isset($exif['Software'])) {
                    $software = strtolower($exif['Software']);
                    if (str_contains($software, 'photoshop') || str_contains($software, 'gimp')) {
                        $errors[] = 'Image edited with graphics software';
                        $score -= 20;
                    }
                }

                // Check for suspicious EXIF modifications
                if (isset($exif['DateTime']) && isset($exif['DateTimeOriginal'])) {
                    if ($exif['DateTime'] !== $exif['DateTimeOriginal']) {
                        $errors[] = 'EXIF timestamps modified';
                        $score -= 15;
                    }
                }
            }

            // Basic manipulation detection using error metrics
            // This is a simplified check - in production, you'd use more sophisticated methods
            $width = $image->width();
            $height = $image->height();

            // Check for unusual dimensions (too perfect squares, etc.)
            if ($width === $height && $width > 1000) {
                $errors[] = 'Suspicious image dimensions';
                $score -= 10;
            }

            // Check compression artifacts (simplified)
            // In a real implementation, you'd analyze JPEG quantization tables, etc.

        } catch (\Exception $e) {
            $errors[] = 'Image analysis failed: ' . $e->getMessage();
            $score = 0;
        }

        return [
            'authentic' => empty($errors),
            'score' => max(0, $score),
            'errors' => $errors,
        ];
    }

    /**
     * Generate perceptual hash for duplicate detection
     */
    private function generatePerceptualHash(string $filePath): ?string
    {
        try {
            $image = $this->imageManager->read($filePath);

            // Resize to 8x8 for simple hash
            $resized = $image->scale(8, 8)->greyscale();

            $pixels = [];
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $color = $resized->pickColor($x, $y);
                    $pixels[] = ($color[0] + $color[1] + $color[2]) / 3; // Average RGB
                }
            }

            // Calculate mean
            $mean = array_sum($pixels) / count($pixels);

            // Generate binary hash
            $hash = '';
            foreach ($pixels as $pixel) {
                $hash .= $pixel > $mean ? '1' : '0';
            }

            return $hash;

        } catch (\Exception $e) {
            Log::warning('Perceptual hash generation failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check for duplicate documents using perceptual hash
     */
    private function checkForDuplicates(Model $document, string $perceptualHash): array
    {
        $modelClass = get_class($document);
        $table = $document->getTable();

        // Calculate Hamming distance threshold (allow for small differences)
        $threshold = 10; // Maximum different bits for similarity

        $duplicates = $modelClass::where('perceptual_hash', '!=', null)
            ->where('id', '!=', $document->id)
            ->whereRaw("BIT_COUNT(CONV(perceptual_hash, 2, 10) ^ CONV(?, 2, 10)) <= ?", [$perceptualHash, $threshold])
            ->exists();

        return [
            'has_duplicates' => $duplicates,
        ];
    }

    /**
     * Extract text from image using OCR
     */
    private function extractText(string $filePath): ?string
    {
        try {
            $ocr = new TesseractOCR($filePath);
            $text = $ocr->run();

            // Clean up the text
            $text = trim($text);
            return $text ?: null;

        } catch (\Exception $e) {
            Log::warning('OCR extraction failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mark verification as failed
     */
    private function failVerification(Model $document, array $errors): array
    {
        $document->update([
            'verification_status' => 'failed',
            'verification_errors' => json_encode($errors),
            'verified_at' => now(),
            'authenticity_score' => 0,
        ]);

        return [
            'status' => 'failed',
            'score' => 0,
            'errors' => $errors,
        ];
    }

    /**
     * Quick verification for upload time (non-blocking)
     */
    public function quickVerify(Model $document): array
    {
        $filePath = decrypt($document->file_path);
        $fullPath = Storage::disk('private')->path($filePath);

        if (!file_exists($fullPath)) {
            return $this->failVerification($document, ['File not found']);
        }

        $errors = [];
        $score = 100.0;

        // Basic hash check
        $currentHash = hash_file('sha256', $fullPath);
        if ($document->file_hash !== $currentHash) {
            $errors[] = 'File hash mismatch';
            $score -= 50;
        }

        // Basic file size check (suspiciously small files might be manipulated)
        $fileSize = filesize($fullPath);
        if ($fileSize < 1024) { // Less than 1KB
            $errors[] = 'File suspiciously small';
            $score -= 20;
        }

        $status = empty($errors) ? 'passed' : 'failed';

        // Update with basic verification results
        $document->update([
            'verification_status' => $status,
            'verification_errors' => $errors ? json_encode($errors) : null,
            'verified_at' => now(),
            'authenticity_score' => $score,
        ]);

        return [
            'status' => $status,
            'score' => $score,
            'errors' => $errors,
        ];
    }
}
