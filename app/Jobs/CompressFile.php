<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompressFile implements ShouldQueue
{
    use Queueable;

    protected string $filePath;
    protected string $disk;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, string $disk = 'public')
    {
        $this->filePath = $filePath;
        $this->disk = $disk;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!Storage::disk($this->disk)->exists($this->filePath)) {
                Log::warning("File does not exist for compression: {$this->filePath}");
                return;
            }

            $extension = strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                Log::info("Skipping compression for non-image file: {$this->filePath}");
                return;
            }

            $fullPath = Storage::disk($this->disk)->path($this->filePath);

            // Get original file size
            $originalSize = filesize($fullPath);

            // Load image
            $imageInfo = getimagesize($fullPath);
            if (!$imageInfo) {
                Log::warning("Invalid image file: {$this->filePath}");
                return;
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mime = $imageInfo['mime'];

            // Create image resource
            $image = null;
            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($fullPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($fullPath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($fullPath);
                    break;
                case 'image/webp':
                    $image = imagecreatefromwebp($fullPath);
                    break;
                default:
                    Log::warning("Unsupported image format: {$mime}");
                    return;
            }

            if (!$image) {
                Log::warning("Failed to create image resource for: {$this->filePath}");
                return;
            }

            // Resize if too large (max 2000x2000)
            if ($width > 2000 || $height > 2000) {
                $ratio = min(2000 / $width, 2000 / $height);
                $newWidth = round($width * $ratio);
                $newHeight = round($height * $ratio);

                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resizedImage;
                $width = $newWidth;
                $height = $newHeight;
            }

            // Save compressed image
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $fullPath, 85); // 85% quality
                    break;
                case 'png':
                    imagepng($image, $fullPath, 8); // PNG compression level
                    break;
                case 'gif':
                    imagegif($image, $fullPath);
                    break;
                case 'webp':
                    imagewebp($image, $fullPath, 85);
                    break;
            }

            imagedestroy($image);

            $newSize = filesize($fullPath);
            $compressionRatio = round((1 - ($newSize / $originalSize)) * 100, 2);

            Log::info("File compressed successfully", [
                'file' => $this->filePath,
                'original_size' => $this->formatBytes($originalSize),
                'new_size' => $this->formatBytes($newSize),
                'compression_ratio' => "{$compressionRatio}%"
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to compress file: {$this->filePath}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
