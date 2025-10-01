<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ElectionAppeal;
use App\Models\AppealDocument;
use App\Models\Admin;
use App\Services\Appeal\AppealService;
use App\Services\Appeal\AppealNotificationService;
use App\Enums\Appeal\AppealStatus;
use App\Enums\Appeal\AppealPriority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AppealAdminController extends Controller
{
    public function __construct(
        private AppealService $appealService,
        private AppealNotificationService $notificationService
    ) {}

    private function getAuthenticatedAdmin(): Admin
    {
        $admin = Admin::find(Auth::guard('admin')->id());
        if (!$admin) {
            throw new \RuntimeException('Admin not found');
        }
        return $admin;
    }

    /**
     * Display a listing of all appeals
     */
    public function index(Request $request)
    {
        $query = ElectionAppeal::with(['appellant', 'election', 'assignedTo', 'documents']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned admin
        if ($request->has('assigned_to') && $request->assigned_to !== '') {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by election
        if ($request->has('election_id') && $request->election_id !== '') {
            $query->where('election_id', $request->election_id);
        }

        // Search by appellant name or appeal title
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('appellant', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $appeals = $query->orderBy('priority', 'desc')
            ->orderBy('submitted_at', 'asc')
            ->paginate(20);

        $stats = $this->appealService->getStatistics();
        $admins = Admin::select('id', 'name')->get();
        $statuses = AppealStatus::getSelectOptions();
        $priorities = AppealPriority::getSelectOptions();

        return view('admin.appeals.index', compact('appeals', 'stats', 'admins', 'statuses', 'priorities'));
    }

    /**
     * Display the specified appeal
     */
    public function show(ElectionAppeal $appeal)
    {
        $appeal->load(['appellant', 'election', 'assignedTo', 'documents', 'documents.reviewer']);

        return view('admin.appeals.show', compact('appeal'));
    }

    /**
     * Assign appeal to an admin
     */
    public function assign(Request $request, ElectionAppeal $appeal): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:admins,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = Admin::findOrFail($request->admin_id);

            $this->appealService->assignAppeal($appeal, $admin);

            // Send notification
            $this->notificationService->notifyAppealAssigned($appeal);

            return response()->json([
                'success' => true,
                'message' => 'Appeal assigned successfully.',
                'assigned_to' => $admin->name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign appeal.'
            ], 500);
        }
    }

    /**
     * Update appeal status
     */
    public function updateStatus(Request $request, ElectionAppeal $appeal): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', array_keys(AppealStatus::getSelectOptions())),
            'review_notes' => 'nullable|string|max:5000',
            'resolution' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = $this->getAuthenticatedAdmin();

            $this->appealService->updateStatus(
                $appeal,
                AppealStatus::from($request->status),
                $admin,
                $request->review_notes,
                $request->resolution
            );

            // Send notification
            $this->notificationService->notifyAppealStatusUpdate($appeal);

            return response()->json([
                'success' => true,
                'message' => 'Appeal status updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appeal status.'
            ], 500);
        }
    }

    /**
     * Escalate appeal priority
     */
    public function escalate(Request $request, ElectionAppeal $appeal): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = $this->getAuthenticatedAdmin();

            $this->appealService->escalateAppeal($appeal, $admin, $request->reason);

            // Send notification
            $this->notificationService->notifyAppealEscalated($appeal, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Appeal escalated successfully.',
                'new_priority' => $appeal->fresh()->priority->label()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Review appeal document
     */
    public function reviewDocument(Request $request, AppealDocument $document): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = $this->getAuthenticatedAdmin();

            if ($request->status === 'approved') {
                $document->approve($admin, $request->review_notes);
            } else {
                $document->reject($admin, $request->review_notes ?: 'Document rejected by reviewer');
            }

            // Send notification
            $this->notificationService->notifyDocumentReview($document);

            return response()->json([
                'success' => true,
                'message' => 'Document review completed successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to review document.'
            ], 500);
        }
    }

    /**
     * Bulk assign appeals
     */
    public function bulkAssign(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'appeal_ids' => 'required|array|min:1',
            'appeal_ids.*' => 'exists:election_appeals,id',
            'admin_id' => 'required|exists:admins,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = Admin::findOrFail($request->admin_id);
            $count = $this->appealService->bulkAssignAppeals($request->appeal_ids, $admin);

            return response()->json([
                'success' => true,
                'message' => "{$count} appeal(s) assigned successfully.",
                'assigned_count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign appeals.'
            ], 500);
        }
    }

    /**
     * Get appeals statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->appealService->getStatistics();

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }

    /**
     * Download appeal document
     */
    public function downloadDocument(AppealDocument $document)
    {
        if (!Storage::exists($document->path)) {
            abort(404, 'Document not found.');
        }

        return Storage::download($document->path, $document->original_filename);
    }
}
