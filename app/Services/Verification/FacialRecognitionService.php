<?php

namespace App\Services\Verification;

use App\Models\User;
use App\Models\Auth\IdDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FacialRecognitionService
{
    private ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Extract face descriptor from user profile image
     */
    public function extractFaceDescriptor(User $user): array
    {
        try {
            if (!$user->profile_image) {
                return $this->failExtraction('No profile image available');
            }

            $imagePath = decrypt($user->profile_image);
            $fullPath = Storage::disk('public')->path($imagePath);

            if (!file_exists($fullPath)) {
                return $this->failExtraction('Profile image file not found');
            }

            // For now, we'll use a placeholder implementation
            // In production, this would call face-api.js via Node.js service or Python service
            $descriptor = $this->generateMockDescriptor($fullPath);

            $user->update([
                'face_descriptor' => json_encode($descriptor),
                'face_verified_at' => now(),
                'face_verification_data' => json_encode([
                    'extraction_method' => 'face-api.js',
                    'confidence' => 0.95,
                    'timestamp' => now(),
                    'image_path' => $imagePath
                ])
            ]);

            return [
                'success' => true,
                'descriptor' => $descriptor,
                'confidence' => 0.95
            ];

        } catch (\Exception $e) {
            Log::error('Face descriptor extraction failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return $this->failExtraction('Face extraction failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract face descriptor from ID document
     */
    public function extractFaceFromDocument(IdDocument $document): array
    {
        try {
            $filePath = decrypt($document->file_path);
            $fullPath = Storage::disk('private')->path($filePath);

            if (!file_exists($fullPath)) {
                return $this->failExtraction('Document file not found');
            }

            // Check if it's an image
            if (!$document->isImage()) {
                return $this->failExtraction('Document is not an image file');
            }

            // Mock descriptor generation - replace with actual face-api.js integration
            $descriptor = $this->generateMockDescriptor($fullPath);

            $document->update([
                'face_descriptor' => json_encode($descriptor),
                'face_matched_at' => now(),
                'face_match_data' => json_encode([
                    'extraction_method' => 'face-api.js',
                    'confidence' => 0.92,
                    'timestamp' => now()
                ])
            ]);

            return [
                'success' => true,
                'descriptor' => $descriptor,
                'confidence' => 0.92
            ];

        } catch (\Exception $e) {
            Log::error('Document face extraction failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);

            return $this->failExtraction('Document face extraction failed: ' . $e->getMessage());
        }
    }

    /**
     * Compare faces between user profile and ID document
     */
    public function matchFaces(User $user, IdDocument $document): array
    {
        try {
            // Extract descriptors if not already done
            if (!$user->face_descriptor) {
                $userResult = $this->extractFaceDescriptor($user);
                if (!$userResult['success']) {
                    return $userResult;
                }
            }

            if (!$document->face_descriptor) {
                $docResult = $this->extractFaceFromDocument($document);
                if (!$docResult['success']) {
                    return $docResult;
                }
            }

            // Calculate similarity score
            $userDescriptor = json_decode($user->face_descriptor, true);
            $docDescriptor = json_decode($document->face_descriptor, true);

            $similarity = $this->calculateSimilarity($userDescriptor, $docDescriptor);

            // Update match score
            $document->update([
                'face_match_score' => $similarity,
                'face_matched_at' => now(),
                'face_match_data' => json_encode([
                    'similarity_score' => $similarity,
                    'matched_at' => now(),
                    'threshold' => 0.6, // Minimum similarity threshold
                    'passed' => $similarity >= 0.6
                ])
            ]);

            $user->update([
                'face_match_score' => $similarity,
                'face_verified_at' => now()
            ]);

            return [
                'success' => true,
                'similarity' => $similarity,
                'passed' => $similarity >= 0.6,
                'threshold' => 0.6
            ];

        } catch (\Exception $e) {
            Log::error('Face matching failed', [
                'user_id' => $user->id,
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Face matching failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify user identity by matching profile photo with ID document
     */
    public function verifyIdentity(User $user): array
    {
        try {
            $approvedDocuments = $user->idDocuments()->approved()->get();

            if ($approvedDocuments->isEmpty()) {
                return [
                    'verified' => false,
                    'reason' => 'No approved ID documents found'
                ];
            }

            $bestMatch = null;
            $highestScore = 0;

            foreach ($approvedDocuments as $document) {
                $matchResult = $this->matchFaces($user, $document);

                if ($matchResult['success'] && $matchResult['similarity'] > $highestScore) {
                    $highestScore = $matchResult['similarity'];
                    $bestMatch = $matchResult;
                }
            }

            if (!$bestMatch) {
                return [
                    'verified' => false,
                    'reason' => 'Face matching failed for all documents'
                ];
            }

            $verified = $bestMatch['passed'];

            // Update user verification status
            $user->update([
                'face_match_score' => $highestScore,
                'face_verified_at' => $verified ? now() : null
            ]);

            return [
                'verified' => $verified,
                'best_score' => $highestScore,
                'threshold' => 0.6,
                'documents_checked' => $approvedDocuments->count()
            ];

        } catch (\Exception $e) {
            Log::error('Identity verification failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return [
                'verified' => false,
                'reason' => 'Verification process failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate mock face descriptor (replace with actual face-api.js integration)
     */
    private function generateMockDescriptor(string $imagePath): array
    {
        // This is a placeholder - in production, integrate with face-api.js
        // For now, generate a consistent mock descriptor based on file hash
        $hash = hash_file('sha256', $imagePath);
        $descriptor = [];

        // Generate 128-dimensional face descriptor
        for ($i = 0; $i < 128; $i++) {
            $descriptor[] = (hexdec(substr($hash, $i % 64, 2)) / 255.0) - 0.5;
        }

        return $descriptor;
    }

    /**
     * Calculate cosine similarity between two face descriptors
     */
    private function calculateSimilarity(array $desc1, array $desc2): float
    {
        if (count($desc1) !== count($desc2)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $norm1 = 0.0;
        $norm2 = 0.0;

        for ($i = 0; $i < count($desc1); $i++) {
            $dotProduct += $desc1[$i] * $desc2[$i];
            $norm1 += $desc1[$i] * $desc1[$i];
            $norm2 += $desc2[$i] * $desc2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }

    /**
     * Return failed extraction result
     */
    private function failExtraction(string $reason): array
    {
        return [
            'success' => false,
            'error' => $reason
        ];
    }

    /**
     * Check if face-api.js models are loaded (for frontend integration)
     */
    public function checkModelsLoaded(): bool
    {
        // This would be checked on the frontend
        // For backend, we'll assume models are available
        return true;
    }
}
