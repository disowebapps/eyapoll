<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\IdDocument;

class DocumentController extends Controller
{
    public function view(IdDocument $document)
    {
        // Security: Ensure user owns the document and it's approved
        if ($document->user_id !== Auth::id() || $document->status !== 'approved') {
            abort(404);
        }
        
        try {
            $filePath = $document->file_path;
            
            if (!Storage::exists($filePath)) {
                abort(404);
            }
            
            $fileContent = Storage::get($filePath);
            $fileName = pathinfo($filePath, PATHINFO_BASENAME);
            $mimeType = Storage::mimeType($filePath);
            
            return response($fileContent, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'private, no-cache'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Document view failed', [
                'document_id' => $document->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            abort(404);
        }
    }
}