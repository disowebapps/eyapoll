<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SecureFileService
{
    protected $disk = 'secure';

    /**
     * Store a file securely with encryption
     */
    public function store($file, string $path = null): string
    {
        // Generate a unique filename
        $filename = $path ?: $this->generateUniqueFilename($file);

        // Read file content
        $content = file_get_contents($file->getRealPath());

        // Encrypt the content
        $encryptedContent = Crypt::encrypt($content);

        // Store encrypted content
        Storage::disk($this->disk)->put($filename, $encryptedContent);

        return $filename;
    }

    /**
     * Retrieve and decrypt a file
     */
    public function retrieve(string $filename)
    {
        if (!Storage::disk($this->disk)->exists($filename)) {
            // Try backward compatibility: check if file exists in old locations
            if ($this->migrateLegacyFile($filename)) {
                // File migrated, now retrieve
                return $this->retrieve($filename);
            }
            return null;
        }

        $encryptedContent = Storage::disk($this->disk)->get($filename);

        try {
            return Crypt::decrypt($encryptedContent);
        } catch (\Exception $e) {
            // If decryption fails, try unencrypted (legacy)
            return $encryptedContent;
        }
    }

    /**
     * Generate a secure access URL with time-limited token
     */
    public function generateAccessUrl(string $filename, int $minutes = 60): string
    {
        return URL::signedRoute('secure-files.view', [
            'filename' => $filename
        ], now()->addMinutes($minutes));
    }

    /**
     * Check if access token is valid
     */
    public function validateAccess(string $filename, string $signature): bool
    {
        try {
            $url = route('secure-files.view', ['filename' => $filename]);
            return URL::hasValidSignature($url . '?signature=' . $signature);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete a secure file
     */
    public function delete(string $filename): bool
    {
        return Storage::disk($this->disk)->delete($filename);
    }

    /**
     * Check if file exists
     */
    public function exists(string $filename): bool
    {
        return Storage::disk($this->disk)->exists($filename);
    }

    /**
     * Get file size
     */
    public function size(string $filename): int
    {
        return Storage::disk($this->disk)->size($filename);
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename($file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Migrate existing file to secure storage
     */
    public function migrateFile(string $oldPath, string $disk = 'public'): ?string
    {
        if (!Storage::disk($disk)->exists($oldPath)) {
            return null;
        }

        $content = Storage::disk($disk)->get($oldPath);
        $encryptedContent = Crypt::encrypt($content);

        $filename = Str::uuid() . '.' . pathinfo($oldPath, PATHINFO_EXTENSION);
        Storage::disk($this->disk)->put($filename, $encryptedContent);

        return $filename;
    }

    /**
     * Try to migrate legacy file from old storage locations
     */
    private function migrateLegacyFile(string $filename): bool
    {
        // Try local disk (storage/app/private)
        $legacyPath = 'documents/' . $filename;
        if (Storage::disk('local')->exists($legacyPath)) {
            $this->migrateFile($legacyPath, 'local');
            Storage::disk('local')->delete($legacyPath); // Remove old file
            return true;
        }

        // Try public disk
        if (Storage::disk('public')->exists($legacyPath)) {
            $this->migrateFile($legacyPath, 'public');
            Storage::disk('public')->delete($legacyPath); // Remove old file
            return true;
        }

        return false;
    }
}