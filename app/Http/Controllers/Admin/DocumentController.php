<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\IdDocument;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function view(IdDocument $document)
    {
        \Illuminate\Support\Facades\Log::info('DocumentController view called', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'document_type' => $document->document_type,
            'status' => $document->status,
            'encrypted_path' => $document->file_path,
            'request_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'request_url' => request()->fullUrl(),
            'request_method' => request()->method()
        ]);

        // Fast authentication check
        if (!auth('admin')->check()) {
            \Illuminate\Support\Facades\Log::warning('Document view access denied: admin not authenticated', [
                'document_id' => $document->id,
                'request_ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            abort(403, 'Unauthorized access');
        }

        $admin = auth('admin')->user();
        \Illuminate\Support\Facades\Log::info('Admin authenticated for document view', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name ?? 'Unknown',
            'admin_email' => $admin->email ?? 'Unknown'
        ]);

        // Decrypt file path with fallback
        $filePath = null;
        try {
            $filePath = decrypt($document->file_path);
            \Illuminate\Support\Facades\Log::info('File path decrypted successfully', [
                'document_id' => $document->id,
                'encrypted_path' => $document->file_path,
                'decrypted_path' => $filePath
            ]);
        } catch (\Exception $e) {
            $filePath = $document->file_path;
            \Illuminate\Support\Facades\Log::warning('File path decryption failed, using raw path', [
                'document_id' => $document->id,
                'raw_path' => $filePath,
                'decryption_error' => $e->getMessage()
            ]);
        }

        // Try multiple possible path formats
        $possiblePaths = [
            'kyc-documents/' . $filePath,  // Direct file
            'kyc-documents/' . dirname($filePath) . '/' . basename($filePath),  // UUID/filename
        ];

        // If the decrypted path looks like just a UUID.jpg, try to find the actual file
        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.(jpg|jpeg|png|pdf)$/i', $filePath)) {
            $uuid = pathinfo($filePath, PATHINFO_FILENAME);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            // Look for files in subdirectories that match this UUID
            $allFiles = Storage::allFiles('kyc-documents');
            foreach ($allFiles as $file) {
                if (str_contains($file, $uuid)) {
                    $possiblePaths[] = $file;
                    \Illuminate\Support\Facades\Log::info('Found matching file for UUID', [
                        'uuid' => $uuid,
                        'matching_file' => $file
                    ]);
                }
            }
        }

        $storagePath = null;
        foreach ($possiblePaths as $path) {
            if (Storage::exists($path)) {
                $storagePath = $path;
                \Illuminate\Support\Facades\Log::info('Found existing file path', [
                    'document_id' => $document->id,
                    'found_path' => $path
                ]);
                break;
            }
        }

        // If no path found, try searching by document type as fallback
        if (!$storagePath) {
            $documentType = $document->document_type;
            $typeString = is_string($documentType) ? $documentType : $documentType->value;

            // Look for files that match the document type
            foreach (Storage::allFiles('kyc-documents') as $file) {
                $fileName = basename($file);
                if (str_contains($fileName, $typeString) ||
                    str_contains(strtolower($fileName), str_replace('_', '', $typeString))) {
                    $storagePath = $file;
                    \Illuminate\Support\Facades\Log::info('Found file by document type match', [
                        'document_id' => $document->id,
                        'document_type' => $typeString,
                        'matching_file' => $file
                    ]);
                    break;
                }
            }
        }

        // If still no path found, use the first possible path for error reporting
        if (!$storagePath) {
            $storagePath = $possiblePaths[0];
        }
        \Illuminate\Support\Facades\Log::info('Storage path resolution attempted', [
            'document_id' => $document->id,
            'decrypted_file_path' => $filePath,
            'possible_paths' => $possiblePaths,
            'resolved_path' => $storagePath,
            'full_storage_path' => Storage::path($storagePath)
        ]);

        // Check file existence
        $fileExists = Storage::exists($storagePath);
        \Illuminate\Support\Facades\Log::info('File existence check', [
            'document_id' => $document->id,
            'storage_path' => $storagePath,
            'exists' => $fileExists,
            'disk' => config('filesystems.default'),
            'storage_disk_root' => config('filesystems.disks.' . config('filesystems.default') . '.root')
        ]);

        if (!$fileExists) {
            // Log all files in the kyc-documents directory for debugging
            $allFiles = Storage::allFiles('kyc-documents');
            $documentType = $document->document_type;
            $typeString = is_string($documentType) ? $documentType : $documentType->value;

            \Illuminate\Support\Facades\Log::error('Document file not found after all search attempts', [
                'document_id' => $document->id,
                'user_id' => $document->user_id,
                'document_type' => $typeString,
                'searched_path' => $storagePath,
                'decrypted_path' => $filePath,
                'uuid_extracted' => preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i', $filePath, $matches) ? $matches[0] : 'none',
                'all_kyc_files' => $allFiles,
                'total_files_found' => count($allFiles),
                'files_by_type' => array_filter($allFiles, function($file) use ($typeString) {
                    return str_contains(basename($file), $typeString);
                }),
                'disk_config' => config('filesystems.disks.' . config('filesystems.default'))
            ]);

            // Show a user-friendly error page instead of just 404
            return response()->view('errors.document-not-found', [
                'document' => $document,
                'message' => 'The document file could not be found. It may have been deleted or never uploaded properly.'
            ], 404);
        }

        // Get file system path for direct access
        $fullFilePath = Storage::path($storagePath);
        \Illuminate\Support\Facades\Log::info('Full file system path', [
            'document_id' => $document->id,
            'full_file_path' => $fullFilePath,
            'file_exists_on_filesystem' => file_exists($fullFilePath),
            'is_readable' => is_readable($fullFilePath)
        ]);

        // Get MIME type efficiently
        $mimeType = mime_content_type($fullFilePath) ?: 'application/octet-stream';
        $fileSize = filesize($fullFilePath);

        \Illuminate\Support\Facades\Log::info('File metadata retrieved', [
            'document_id' => $document->id,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'extension' => pathinfo($fullFilePath, PATHINFO_EXTENSION),
            'is_image' => str_starts_with($mimeType, 'image/'),
            'is_pdf' => $mimeType === 'application/pdf'
        ]);

        // Security and performance headers
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'private, max-age=300', // 5 minute browser cache
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'",
            'X-Document-ID' => $document->id,
            'X-File-Size' => $fileSize,
            'X-Mime-Type' => $mimeType
        ];

        \Illuminate\Support\Facades\Log::info('Serving document file', [
            'document_id' => $document->id,
            'file_path' => $fullFilePath,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'headers' => $headers,
            'download_url' => route('admin.document.view', $document->id),
            'full_download_url' => url(route('admin.document.view', $document->id))
        ]);

        // Use response()->file() for optimal performance with large files
        // This streams the file instead of loading it into memory
        return response()->file($fullFilePath, $headers);
    }
}
