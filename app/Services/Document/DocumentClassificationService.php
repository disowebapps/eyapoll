<?php

namespace App\Services;

use App\Models\Auth\IdDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class DocumentClassificationService
{
    /**
     * Document type keywords and patterns
     */
    private const DOCUMENT_PATTERNS = [
        'national_id' => [
            'keywords' => ['national id', 'identity card', 'id card', 'citizen', 'national'],
            'patterns' => ['/national.*id/i', '/identity.*card/i', '/id.*card/i'],
            'confidence' => 0.8
        ],
        'passport' => [
            'keywords' => ['passport', 'travel document', 'international'],
            'patterns' => ['/passport/i', '/travel.*document/i'],
            'confidence' => 0.9
        ],
        'drivers_license' => [
            'keywords' => ['driver', 'license', 'driving', 'motor vehicle'],
            'patterns' => ['/driver.*license/i', '/driving.*license/i', '/motor.*vehicle/i'],
            'confidence' => 0.85
        ]
    ];

    /**
     * Classify a document based on OCR text and metadata
     */
    public function classifyDocument(IdDocument $document): array
    {
        try {
            $classification = [
                'document_type' => null,
                'confidence' => 0.0,
                'method' => 'rule_based',
                'evidence' => []
            ];

            // Get OCR text if available
            $ocrText = $document->ocr_text;

            // If no OCR text, try to extract it
            if (!$ocrText && $document->isImage()) {
                $ocrText = $this->extractText($document);
                if ($ocrText) {
                    $document->update(['ocr_text' => $ocrText]);
                }
            }

            // Analyze filename and path for clues
            $filename = pathinfo(decrypt($document->file_path), PATHINFO_FILENAME);
            $filenameClues = $this->analyzeFilename($filename);

            // Analyze OCR text
            $textClues = $ocrText ? $this->analyzeText($ocrText) : [];

            // Combine evidence
            $allClues = array_merge($filenameClues, $textClues);

            // Determine best classification
            $bestMatch = $this->determineBestMatch($allClues);

            if ($bestMatch) {
                $classification['document_type'] = $bestMatch['type'];
                $classification['confidence'] = $bestMatch['confidence'];
                $classification['evidence'] = $bestMatch['evidence'];
            }

            // Update document with classification results
            $document->update([
                'document_category' => $classification['document_type'],
                'classification_confidence' => $classification['confidence']
            ]);

            return $classification;

        } catch (\Exception $e) {
            Log::error('Document classification failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);

            return [
                'document_type' => null,
                'confidence' => 0.0,
                'method' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Analyze filename for document type clues
     */
    private function analyzeFilename(string $filename): array
    {
        $clues = [];

        foreach (self::DOCUMENT_PATTERNS as $type => $config) {
            $score = 0;
            $evidence = [];

            // Check for type keywords in filename
            foreach ($config['keywords'] as $keyword) {
                if (stripos($filename, $keyword) !== false) {
                    $score += 0.3;
                    $evidence[] = "Filename contains '$keyword'";
                }
            }

            // Check for patterns in filename
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $filename)) {
                    $score += 0.4;
                    $evidence[] = "Filename matches pattern '$pattern'";
                }
            }

            if ($score > 0) {
                $clues[] = [
                    'type' => $type,
                    'score' => min($score, 1.0),
                    'evidence' => $evidence,
                    'source' => 'filename'
                ];
            }
        }

        return $clues;
    }

    /**
     * Analyze OCR text for document type clues
     */
    private function analyzeText(string $text): array
    {
        $clues = [];

        foreach (self::DOCUMENT_PATTERNS as $type => $config) {
            $score = 0;
            $evidence = [];

            // Check for keywords in text
            foreach ($config['keywords'] as $keyword) {
                if (stripos($text, $keyword) !== false) {
                    $score += 0.4;
                    $evidence[] = "Text contains '$keyword'";
                }
            }

            // Check for regex patterns in text
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $text)) {
                    $score += 0.5;
                    $evidence[] = "Text matches pattern '$pattern'";
                }
            }

            if ($score > 0) {
                $clues[] = [
                    'type' => $type,
                    'score' => min($score, 1.0),
                    'evidence' => $evidence,
                    'source' => 'text'
                ];
            }
        }

        return $clues;
    }

    /**
     * Determine the best matching document type
     */
    private function determineBestMatch(array $clues): ?array
    {
        if (empty($clues)) {
            return null;
        }

        // Group clues by type
        $typeScores = [];
        foreach ($clues as $clue) {
            $type = $clue['type'];
            if (!isset($typeScores[$type])) {
                $typeScores[$type] = [
                    'total_score' => 0,
                    'evidence' => [],
                    'sources' => []
                ];
            }

            $typeScores[$type]['total_score'] += $clue['score'];
            $typeScores[$type]['evidence'] = array_merge(
                $typeScores[$type]['evidence'],
                $clue['evidence']
            );
            $typeScores[$type]['sources'][] = $clue['source'];
        }

        // Find the type with highest score
        $bestType = null;
        $bestScore = 0;
        $bestEvidence = [];

        foreach ($typeScores as $type => $data) {
            $avgScore = $data['total_score'] / count($data['sources']);
            $weightedScore = $avgScore * self::DOCUMENT_PATTERNS[$type]['confidence'];

            if ($weightedScore > $bestScore) {
                $bestScore = $weightedScore;
                $bestType = $type;
                $bestEvidence = $data['evidence'];
            }
        }

        if ($bestType && $bestScore >= 0.5) { // Minimum confidence threshold
            return [
                'type' => $bestType,
                'confidence' => round($bestScore, 4),
                'evidence' => array_unique($bestEvidence)
            ];
        }

        return null;
    }

    /**
     * Extract text from document using OCR
     */
    private function extractText(IdDocument $document): ?string
    {
        try {
            $filePath = decrypt($document->file_path);
            $fullPath = Storage::disk('private')->path($filePath);

            if (!file_exists($fullPath)) {
                return null;
            }

            $ocr = new TesseractOCR($fullPath);
            $text = $ocr->run();

            return trim($text) ?: null;

        } catch (\Exception $e) {
            Log::warning('OCR extraction failed for classification', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Batch classify multiple documents
     */
    public function classifyDocuments(array $documents): array
    {
        $results = [];

        foreach ($documents as $document) {
            if ($document instanceof IdDocument) {
                $results[$document->id] = $this->classifyDocument($document);
            }
        }

        return $results;
    }

    /**
     * Get classification statistics
     */
    public function getClassificationStats(): array
    {
        $total = IdDocument::count();
        $classified = IdDocument::whereNotNull('document_category')->count();
        $byType = IdDocument::whereNotNull('document_category')
            ->selectRaw('document_category, COUNT(*) as count')
            ->groupBy('document_category')
            ->pluck('count', 'document_category')
            ->toArray();

        $avgConfidence = IdDocument::whereNotNull('classification_confidence')
            ->avg('classification_confidence') ?? 0;

        return [
            'total_documents' => $total,
            'classified_documents' => $classified,
            'classification_rate' => $total > 0 ? round(($classified / $total) * 100, 2) : 0,
            'documents_by_type' => $byType,
            'average_confidence' => round($avgConfidence, 4)
        ];
    }
}