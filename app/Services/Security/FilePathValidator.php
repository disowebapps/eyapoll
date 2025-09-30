<?php

namespace App\Services\Security;

class FilePathValidator
{
    private const ALLOWED_DIRS = ['candidate-documents', 'kyc-documents', 'election-exports', 'keys'];
    private const ALLOWED_EXTS = ['pdf', 'jpg', 'jpeg', 'png', 'csv', 'pem'];

    public function validatePath(string $path): bool
    {
        // Normalize path for Windows compatibility
        $normalizedPath = str_replace('\\', '/', $path);
        $storagePath = str_replace('\\', '/', storage_path());
        
        // Check if path is within storage directory
        if (!str_starts_with($normalizedPath, $storagePath)) {
            return false;
        }
        
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, self::ALLOWED_EXTS) && $this->isAllowedDir($path);
    }

    private function isAllowedDir(string $path): bool
    {
        foreach (self::ALLOWED_DIRS as $dir) {
            if (str_contains($path, $dir)) return true;
        }
        return false;
    }
}