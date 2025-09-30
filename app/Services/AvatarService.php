<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    /**
     * Generate a cached local avatar URL for a given seed
     */
    public function getAvatarUrl(string $seed, int $size = 64): string
    {
        $cacheKey = "avatar_{$seed}_{$size}";

        return Cache::remember($cacheKey, 86400, function() use ($seed, $size) {
            return $this->generateLocalAvatar($seed, $size);
        });
    }

    /**
     * Generate a local SVG avatar (personas style)
     */
    private function generateLocalAvatar(string $seed, int $size): string
    {
        // Simple hash-based color generation
        $hash = md5($seed);
        $colors = [
            '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b',
            '#10b981', '#ef4444', '#06b6d4', '#84cc16', '#f97316'
        ];

        $colorIndex = hexdec(substr($hash, 0, 1)) % count($colors);
        $backgroundColor = $colors[$colorIndex];

        // Generate simple SVG avatar
        $svg = '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="64" height="64" rx="20" fill="' . $backgroundColor . '"/>';
        $svg .= '<circle cx="32" cy="24" r="12" fill="white" opacity="0.9"/>';
        $svg .= '<path d="M16 48c0-8.8 7.2-16 16-16s16 7.2 16 16" fill="white" opacity="0.9"/>';
        $svg .= '</svg>';

        // Encode as data URL
        $encodedSvg = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return $encodedSvg;
    }

    /**
     * Generate initials-based avatar
     */
    public function getInitialsAvatar(string $name, int $size = 64): string
    {
        $initials = $this->getInitials($name);

        $hash = md5($name);
        $colors = [
            '#3b82f6', '#6366f1', '#8b5cf6', '#ec4899', '#f59e0b',
            '#10b981', '#ef4444', '#06b6d4', '#84cc16', '#f97316'
        ];

        $colorIndex = hexdec(substr($hash, 0, 1)) % count($colors);
        $backgroundColor = $colors[$colorIndex];

        $svg = '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="64" height="64" rx="20" fill="' . $backgroundColor . '"/>';
        $svg .= '<text x="32" y="40" font-family="Arial, sans-serif" font-size="24" font-weight="bold" text-anchor="middle" fill="white">' . $initials . '</text>';
        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Extract initials from name
     */
    private function getInitials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
                if (strlen($initials) >= 2) break;
            }
        }

        return $initials ?: 'U';
    }
}
