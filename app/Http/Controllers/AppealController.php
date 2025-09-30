<?php

namespace App\Http\Controllers;

use App\Models\ElectionAppeal;
use App\Models\AppealDocument;
use App\Models\Election\Election;
use App\Services\Appeal\AppealService;
use App\Services\Appeal\AppealNotificationService;
use App\Enums\Appeal\AppealType;
use App\Enums\Appeal\AppealPriority;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File;

class AppealController extends Controller
{
    public function __construct(
        private AppealService $appealService,
        private AppealNotificationService $notificationService
    ) {}

    /**
     * Display a listing of user's appeals
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = ElectionAppeal::where('appellant_id', $user->id)
            ->with(['election', 'documents']);

        // Filter by status if provided
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type if provided
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $appeals = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('appeals.index', compact('appeals'));
    }

    /**
     * Show the form for creating a new appeal
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get elections the user can appeal for
        $elections = Election::whereHas('votes', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        $appealTypes = AppealType::getSelectOptions();
        $priorities = AppealPriority::getSelectOptions();

        return view('appeals.create', compact('elections', 'appealTypes', 'priorities'));
    }

    /**
     * Store a newly created appeal
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'election_id' => 'required|exists:elections,id',
            'type' => 'required|in:' . implode(',', array_keys(AppealType::getSelectOptions())),
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'documents' => 'nullable|array|max:3',
            'documents.*' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(5 * 1024) // 5MB
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate appeal deadline
        if (!$this->appealService->validateAppealDeadline($request->election_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Appeal deadline has passed for this election.'
            ], 422);
        }

        try {
            $appeal = $this->appealService->submitAppeal(
                $user,
                $request->election_id,
                AppealType::from($request->type),
                $request->title,
                $request->description,
                $request->only(['additional_data'])
            );

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($appeal, $request->file('documents'));
            }

            // Send notifications
            $this->notificationService->notifyAppealSubmitted($appeal);

            return response()->json([
                'success' => true,
                'message' => 'Appeal submitted successfully.',
                'appeal_id' => $appeal->id,
                'redirect' => route('appeals.show', $appeal->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit appeal. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified appeal
     */
    public function show(ElectionAppeal $appeal)
    {
        $this->authorize('view', $appeal);

        $appeal->load(['election', 'documents', 'assignedTo']);

        return view('appeals.show', compact('appeal'));
    }

    /**
     * Show the form for editing the specified appeal
     */
    public function edit(ElectionAppeal $appeal)
    {
        $this->authorize('update', $appeal);

        // Only allow editing if appeal is still in submitted status
        if ($appeal->status !== \App\Enums\Appeal\AppealStatus::SUBMITTED) {
            abort(403, 'Appeal cannot be edited at this stage.');
        }

        $appealTypes = AppealType::getSelectOptions();
        $priorities = AppealPriority::getSelectOptions();

        return view('appeals.edit', compact('appeal', 'appealTypes', 'priorities'));
    }

    /**
     * Update the specified appeal
     */
    public function update(Request $request, ElectionAppeal $appeal): JsonResponse
    {
        $this->authorize('update', $appeal);

        if ($appeal->status !== \App\Enums\Appeal\AppealStatus::SUBMITTED) {
            return response()->json([
                'success' => false,
                'message' => 'Appeal cannot be updated at this stage.'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'documents' => 'nullable|array|max:3',
            'documents.*' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(5 * 1024)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appeal->update([
                'title' => $request->title,
                'description' => $request->description,
                'appeal_data' => array_merge($appeal->appeal_data ?? [], $request->only(['additional_data']))
            ]);

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $this->handleDocumentUploads($appeal, $request->file('documents'));
            }

            return response()->json([
                'success' => true,
                'message' => 'Appeal updated successfully.',
                'redirect' => route('appeals.show', $appeal->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appeal. Please try again.'
            ], 500);
        }
    }

    /**
     * Handle document uploads for an appeal
     */
    private function handleDocumentUploads(ElectionAppeal $appeal, array $files): void
    {
        foreach ($files as $file) {
            $filename = $file->store('appeals', 'public');

            AppealDocument::create([
                'appeal_id' => $appeal->id,
                'uploaded_by' => Auth::id(),
                'original_filename' => $file->getClientOriginalName(),
                'filename' => basename($filename),
                'mime_type' => $file->getMimeType(),
                'path' => $filename,
                'file_size' => $file->getSize(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
            ]);
        }
    }

    /**
     * Download an appeal document
     */
    public function downloadDocument(AppealDocument $document)
    {
        $this->authorize('view', $document->appeal);

        if (!Storage::exists($document->path)) {
            abort(404, 'Document not found.');
        }

        return Storage::download($document->path, $document->original_filename);
    }
}
