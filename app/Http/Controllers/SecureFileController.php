<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\Security\SecureFileService;

class SecureFileController extends Controller
{
    public function __construct(
        private SecureFileService $secureFileService
    ) {}

    /**
     * Serve a secure file with access token validation
     */
    public function view(Request $request, string $filename)
    {
        // Validate the signed URL
        if (!$this->secureFileService->validateAccess($filename, $request->get('signature'))) {
            abort(403, 'Access denied');
        }

        // Retrieve and decrypt the file
        $content = $this->secureFileService->retrieve($filename);

        if ($content === null) {
            abort(404, 'File not found');
        }

        // Determine MIME type from filename
        $mimeType = $this->getMimeType($filename);

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
            'Cache-Control' => 'private, max-age=3600', // Cache for 1 hour
        ]);
    }

    /**
     * Get MIME type from filename
     */
    private function getMimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match($extension) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };
    }
}