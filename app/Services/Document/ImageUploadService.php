<?php

namespace App\Services\Document;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class ImageUploadService
{
    public function uploadProfileImage(UploadedFile $file, $user): string
    {
        $this->validateImageFile($file);
        $this->deleteOldImage($user);

        $extension = $this->getSecureExtension($file);
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $extension;
        $path = $file->storeAs('profiles', $filename, 'public');

        // Queue compression job for the uploaded image
        \App\Jobs\CompressFile::dispatch($path, 'public');

        return $path;
    }
    
    private function validateImageFile(UploadedFile $file): void
    {
        // File size validation (max 2MB)
        if ($file->getSize() > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('File size must not exceed 2MB');
        }
        
        // MIME type validation
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new InvalidArgumentException('Only JPEG, PNG, GIF, and WebP images are allowed');
        }
        
        // Extension validation
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new InvalidArgumentException('Invalid file extension');
        }
        
        // Content validation using getimagesize
        $imageInfo = @getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new InvalidArgumentException('File is not a valid image');
        }
        
        // Dimension validation
        if ($imageInfo[0] > 2000 || $imageInfo[1] > 2000) {
            throw new InvalidArgumentException('Image dimensions must not exceed 2000x2000 pixels');
        }
    }
    
    private function getSecureExtension(UploadedFile $file): string
    {
        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        
        return $mimeToExtension[$file->getMimeType()] ?? 'jpg';
    }
    
    private function deleteOldImage($user): void
    {
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }
    }
}