<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auth\IdDocument;
use App\Models\User;
use App\Services\KycService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class KycReviewController extends Controller
{
    public function __construct(
        private KycService $kycService
    ) {}

    /**
     * Show the KYC review dashboard
     */
    public function index(Request $request)
    {
        $query = IdDocument::with('user');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'pending') {
                // Show documents from users in REVIEW status
                $query->where('status', 'pending')
                      ->whereHas('user', fn($q) => $q->where('status', 'review'));
            } else {
                $query->where('status', $request->status);
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.kyc.index', compact('documents'));
    }

    /**
     * Show individual document for review
     */
    public function show(IdDocument $document)
    {
        $document->load('user');

        return view('admin.kyc.review', compact('document'));
    }

    /**
     * Approve a KYC document
     */
    public function approve(Request $request, IdDocument $document): JsonResponse
    {
        \Illuminate\Support\Facades\Log::info('KYC approval attempt started', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'admin_id' => Auth::guard('admin')->id(),
            'document_status' => $document->status,
            'user_status' => $document->user->status
        ]);

        try {
            $admin = Auth::guard('admin')->user();
            $user = $document->user;

            \Illuminate\Support\Facades\Log::info('Calling KycService approveUser', [
                'user_id' => $user->id,
                'user_status' => $user->status,
                'admin_id' => $admin->id
            ]);

            $this->kycService->approveUser($user, $admin);

            \Illuminate\Support\Facades\Log::info('KYC approval successful', [
                'document_id' => $document->id,
                'user_id' => $user->id,
                'new_user_status' => $user->fresh()->status,
                'new_document_status' => $document->fresh()->status
            ]);

            // Flash success message for display after redirect
            session()->flash('success', 'User approved successfully.');

            return response()->json([
                'success' => true,
                'message' => 'User approved successfully.',
                'redirect' => route('admin.kyc.index')
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('KYC approval failed', [
                'document_id' => $document->id,
                'user_id' => $document->user_id,
                'admin_id' => Auth::guard('admin')->id(),
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Approval failed. Please try again.',
                'debug_info' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Reject a KYC document
     */
    public function reject(Request $request, IdDocument $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = Auth::guard('admin')->user();
            $user = $document->user;

            $this->kycService->rejectUser($user, $admin, $request->rejection_reason);

            // Flash success message for display after redirect
            session()->flash('success', 'User rejected successfully.');

            return response()->json([
                'success' => true,
                'message' => 'User rejected successfully.',
                'redirect' => route('admin.kyc.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rejection failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Log button clicks for debugging
     */
    public function logClick(Request $request)
    {
        $data = $request->validate([
            'button' => 'required|string',
            'document_id' => 'required|integer',
            'url' => 'required|url',
            'timestamp' => 'required|string'
        ]);

        \Illuminate\Support\Facades\Log::info('KYC Button Click Logged', [
            'button' => $data['button'],
            'document_id' => $data['document_id'],
            'url' => $data['url'],
            'timestamp' => $data['timestamp'],
            'admin_id' => auth('admin')->id(),
            'admin_email' => auth('admin')->user()?->email,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip()
        ]);

        return response()->json(['status' => 'logged']);
    }

    /**
     * Get document type label
     */
    private function getDocumentTypeLabel($type): string
    {
        if ($type instanceof \App\Enums\Auth\DocumentType) {
            return $type->label();
        }

        return match($type) {
            'national_id' => 'National ID',
            'passport' => 'Passport',
            'drivers_license' => 'Driver\'s License',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }
}
