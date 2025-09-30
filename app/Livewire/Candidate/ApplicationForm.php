<?php

namespace App\Livewire\Candidate;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Models\Candidate\Candidate;
use App\Models\Candidate\CandidateDocument;
use App\Models\Candidate\PaymentHistory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Payment\PaymentService;

class ApplicationForm extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public $election = null;
    public $totalSteps = 4; // Will be 5 if payment required
    public $currentStep = 1;
    public $progress = [];

    // Admin: Election and Voter Selection
    public $selectedElectionId = '';
    public $elections = [];
    public $selectedVoterId = '';
    public $voters = [];
    public $isAdminFlow = false;

    // Step 1: Position Selection
    public $selectedPositionId = '';
    public $positions = [];

    // Step 2: Manifesto
    public $manifesto = '';

    // Step 3: Documents
    public $uploadedDocuments = [
        'cv' => null,
        'certificates' => null,
        'photo' => null,
    ];
    
    // Step 4: Payment (if required)
    public $paymentProof = null;
    public $paymentReference = '';
    
    // Admin payment options
    public $adminPaymentAction = 'waive'; // 'waive', 'manual', 'require_proof'
    public $adminPaymentAmount = 0;
    public $adminPaymentNotes = '';

    // Step 4: Review
    public $acceptTerms = false;
    public $isSubmitting = false;
    
    // Computed properties
    public $requiresPayment = false;

    protected $listeners = ['refreshComponent' => '$refresh'];

    protected $rules = [
        'selectedPositionId' => 'required|exists:positions,id',
        'manifesto' => 'required|string|min:50|max:2000',
        'uploadedDocuments.cv' => 'required|file|mimes:pdf,doc,docx|max:10240',
        'uploadedDocuments.certificates' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        'uploadedDocuments.photo' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
        'paymentProof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        'paymentReference' => 'nullable|string|max:100',
        'acceptTerms' => 'required|accepted',
    ];

    protected $messages = [
        'selectedPositionId.required' => 'Please select a position to apply for.',
        'manifesto.required' => 'Please write your manifesto.',
        'manifesto.min' => 'Your manifesto must be at least 50 characters long.',
        'manifesto.max' => 'Your manifesto cannot exceed 2000 characters.',
        'uploadedDocuments.cv.required' => 'CV/Resume is required.',
        'uploadedDocuments.cv.mimes' => 'CV must be a PDF, DOC, or DOCX file.',
        'uploadedDocuments.cv.max' => 'CV file size cannot exceed 5MB.',
        'uploadedDocuments.certificates.mimes' => 'Certificates must be PDF, DOC, DOCX, or image files.',
        'uploadedDocuments.certificates.max' => 'Certificate file size cannot exceed 5MB.',
        'uploadedDocuments.photo.mimes' => 'Photo must be a JPG or PNG file.',
        'uploadedDocuments.photo.max' => 'Photo file size cannot exceed 5MB.',
        'acceptTerms.required' => 'You must accept the terms and conditions.',
        'acceptTerms.accepted' => 'You must accept the terms and conditions.',
    ];

    public function mount($electionId = null)
    {
        $this->isAdminFlow = auth('admin')->check();
        
        if ($this->isAdminFlow) {
            $this->loadElections();
            if ($electionId) {
                $this->selectedElectionId = $electionId;
                $this->setElection($electionId);
            }
        } else {
            if (!$electionId) {
                abort(404, 'Election ID required');
            }
            $this->setElection($electionId);
        }

        // Only perform authorization and checks if election is set
        if ($this->election) {
            $this->performAuthorizationChecks();
        }

        if ($this->election) {
            $this->initializeForm();
        }
    }

    public function loadPositions()
    {
        if (!$this->election) {
            $this->positions = [];
            return;
        }
        
        $this->positions = Position::where('election_id', $this->election->id)
            ->withCount(['candidates' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'max_selections' => $position->max_selections,
                    'current_candidates' => $position->candidates_count,
                    'is_available' => $position->candidates_count < $position->max_selections,
                ];
            })
            ->toArray();
    }

    public function updatedSelectedElectionId()
    {
        if ($this->selectedElectionId) {
            $this->setElection($this->selectedElectionId);
        }
        $this->updateProgress();
        $this->debouncedSave();
    }

    public function updatedSelectedVoterId()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }

    public function updatedSelectedPositionId()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }

    public function updatedManifesto()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }

    public function updatedUploadedDocuments($value, $key)
    {
        if ($key === 'cv' && $value) {
            $allowedMimes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            if (!in_array($value->getMimeType(), $allowedMimes)) {
                $this->addError('uploadedDocuments.cv', 'CV must be a PDF, DOC, or DOCX file.');
                $this->uploadedDocuments['cv'] = null;
                return;
            }
        }
        
        // Save uploaded file to draft
        if ($value) {
            $this->saveUploadedFile($key, $value);
        }
        
        $this->updateProgress();
    }

    public function updatedPaymentReference()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }

    public function updatedAcceptTerms()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }
    
    public function updatedAdminPaymentAction()
    {
        $this->updateProgress();
        $this->debouncedSave();
    }
    
    public function updatedAdminPaymentAmount()
    {
        $this->debouncedSave();
    }
    
    public function updatedAdminPaymentNotes()
    {
        $this->debouncedSave();
    }

    public function updateTotalSteps()
    {
        $this->totalSteps = $this->requiresPayment() ? 5 : 4;
    }
    
    public function requiresPayment()
    {
        return $this->election->candidate_application_fee > 0 && !$this->isAdminFlow;
    }
    
    public function loadElections()
    {
        $this->elections = Election::where('status', 'upcoming')
            ->orWhere('status', 'candidate_registration')
            ->select('id', 'title', 'candidate_application_fee')
            ->orderBy('title')
            ->get()
            ->toArray();
    }
    

    
    private function setElection($electionId)
    {
        $this->election = Election::find($electionId);
        if (!$this->election) {
            if ($this->isAdminFlow) {
                session()->flash('error', 'Election not found');
                return;
            } else {
                abort(404, 'Election not found');
            }
        }
        
        $this->performAuthorizationChecks();
        $this->initializeForm();
    }
    
    private function performAuthorizationChecks()
    {
        $webUser = auth('web')->user();
        $adminUser = auth('admin')->user();
        $currentUser = $webUser ?? $adminUser;
        
        if (!$currentUser) {
            throw new \Exception('No authenticated user found');
        }
        
        if (!$currentUser->can('apply', $this->election)) {
            $message = app('App\Policies\ElectionPolicy')->getApplicationMessage($this->election);
            $redirectRoute = auth('admin')->check() ? 'admin.candidates.index' : 'candidate.dashboard';
            return redirect()->route($redirectRoute)->with('error', $message);
        }

        // Check if user already has an application (skip for admin flow)
        if (!$this->isAdminFlow) {
            $existingApplication = Candidate::where('user_id', auth()->id())
                ->where('election_id', $this->election->id)
                ->first();

            if ($existingApplication) {
                return redirect()->route('candidate.application', $existingApplication->id)
                    ->with('info', 'You already have an application for this election.');
            }
        }
    }
    
    private function initializeForm()
    {
        if ($this->isAdminFlow) {
            $this->loadVoters();
            $this->totalSteps = 7; // Admin has election + voter + payment steps
            $this->adminPaymentAmount = $this->election ? $this->election->candidate_application_fee : 0;
        }
        
        $this->loadPositions();
        $this->updateTotalSteps();
        
        // Load draft if exists
        $this->loadDraft();
        
        $this->updateProgress();
        
        // Initialize computed property
        $this->requiresPayment = $this->requiresPayment();
    }
    
    public function loadVoters()
    {
        $this->voters = \App\Models\User::select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email
                ];
            })
            ->toArray();
    }

    public function updateProgress()
    {
        if ($this->isAdminFlow) {
            $steps = [
                1 => !empty($this->selectedElectionId),
                2 => !empty($this->selectedVoterId),
                3 => !empty($this->selectedPositionId),
                4 => strlen($this->manifesto) >= 50,
                5 => !is_null($this->uploadedDocuments['cv']),
                6 => !empty($this->adminPaymentAction),
                7 => $this->acceptTerms,
            ];
        } else {
            $steps = [
                1 => !empty($this->selectedPositionId),
                2 => strlen($this->manifesto) >= 50,
                3 => !is_null($this->uploadedDocuments['cv']),
            ];
            
            if ($this->requiresPayment()) {
                $steps[4] = !is_null($this->paymentProof) && !empty($this->paymentReference);
                $steps[5] = $this->acceptTerms;
            } else {
                $steps[4] = $this->acceptTerms;
            }
        }

        $this->progress = [
            'steps' => $steps,
            'completed' => count(array_filter($steps)),
            'total' => $this->totalSteps,
            'percentage' => 0,
        ];

        $this->progress['percentage'] = round(($this->progress['completed'] / $this->totalSteps) * 100);
    }

    public function goToStep($step)
    {
        if ($step < 1 || $step > $this->totalSteps) {
            return;
        }

        // Validate previous steps before allowing navigation
        for ($i = 1; $i < $step; $i++) {
            if (!$this->progress['steps'][$i]) {
                session()->flash('error', 'Please complete step ' . $i . ' before proceeding.');
                return;
            }
        }

        $this->currentStep = $step;
        $this->saveDraft();
    }

    public function nextStep()
    {
        \Log::info('NextStep called', [
            'current_step' => $this->currentStep,
            'total_steps' => $this->totalSteps,
            'uploaded_documents' => array_map(fn($doc) => $doc ? $doc->getClientOriginalName() : null, $this->uploadedDocuments),
            'progress' => $this->progress
        ]);
        
        if ($this->currentStep < $this->totalSteps) {
            // Validate current step
            if (!$this->validateCurrentStep()) {
                \Log::error('Step validation failed', ['step' => $this->currentStep]);
                return;
            }

            $this->currentStep++;
            $this->saveDraft();
            \Log::info('Moved to next step', ['new_step' => $this->currentStep]);
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->saveDraft();
        }
    }

    private function validateCurrentStep()
    {
        $rules = [];

        if ($this->isAdminFlow) {
            switch ($this->currentStep) {
                case 1:
                    $rules['selectedElectionId'] = 'required|exists:elections,id';
                    break;
                case 2:
                    $rules['selectedVoterId'] = 'required|exists:users,id';
                    break;
                case 3:
                    if ($this->election) {
                        $rules['selectedPositionId'] = 'required|exists:positions,id,election_id,' . $this->election->id;
                    } else {
                        $rules['selectedPositionId'] = 'required';
                    }
                    break;
                case 4:
                    $rules['manifesto'] = 'required|string|min:50|max:2000';
                    break;
                case 5:
                    $rules['uploadedDocuments.cv'] = 'required|file|mimes:pdf,doc,docx|max:10240';
                    break;
                case 6:
                    $rules['adminPaymentAction'] = 'required|in:waive,manual,require_proof';
                    if ($this->adminPaymentAction === 'require_proof') {
                        $rules['paymentProof'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                        $rules['paymentReference'] = 'required|string|max:100';
                    }
                    break;
                case 7:
                    $rules['acceptTerms'] = 'required|accepted';
                    break;
            }
        } else {
            switch ($this->currentStep) {
                case 1:
                    $rules['selectedPositionId'] = 'required|exists:positions,id';
                    break;
                case 2:
                    $rules['manifesto'] = 'required|string|min:50|max:2000';
                    break;
                case 3:
                    $rules['uploadedDocuments.cv'] = 'required|file|mimes:pdf,doc,docx|max:10240';
                    break;
                case 4:
                    if ($this->requiresPayment()) {
                        $rules['paymentProof'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
                        $rules['paymentReference'] = 'required|string|max:100';
                    } else {
                        $rules['acceptTerms'] = 'required|accepted';
                    }
                    break;
                case 5:
                    $rules['acceptTerms'] = 'required|accepted';
                    break;
            }
        }

        \Log::info('Admin step validation', [
            'step' => $this->currentStep,
            'is_admin_flow' => $this->isAdminFlow,
            'rules' => array_keys($rules),
            'selected_election_id' => $this->selectedElectionId,
            'selected_voter_id' => $this->selectedVoterId,
            'selected_position_id' => $this->selectedPositionId,
            'positions_available' => count($this->positions),
            'election_set' => !is_null($this->election),
            'cv_uploaded' => !is_null($this->uploadedDocuments['cv']),
        ]);

        try {
            $this->validate($rules);
            \Log::info('Step validation passed', [
                'step' => $this->currentStep,
                'is_admin_flow' => $this->isAdminFlow
            ]);
            return true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Step validation failed', [
                'step' => $this->currentStep,
                'is_admin_flow' => $this->isAdminFlow,
                'errors' => $e->errors(),
                'data' => [
                    'selectedElectionId' => $this->selectedElectionId,
                    'selectedVoterId' => $this->selectedVoterId,
                    'selectedPositionId' => $this->selectedPositionId,
                ]
            ]);
            $this->addError(key($e->errors()), current(current($e->errors())));
            return false;
        }
    }

    public function removeDocument($type)
    {
        $this->uploadedDocuments[$type] = null;
        $this->updateProgress();
    }
    
    public function removePaymentProof()
    {
        $this->paymentProof = null;
        $this->updateProgress();
    }

    public function submitApplication()
    {
        // Add conditional payment validation
        $rules = $this->rules;
        if ($this->requiresPayment()) {
            $rules['paymentProof'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:10240';
            $rules['paymentReference'] = 'required|string|max:100';
        }
        
        $this->validate($rules);

        $this->isSubmitting = true;

        try {
            DB::beginTransaction();

            // Check if user can still apply using policy
            $user = auth('web')->user() ?? auth('admin')->user();
            if (!$user->can('apply', $this->election)) {
                $message = app('App\Policies\ElectionPolicy')->getApplicationMessage($this->election);
                session()->flash('error', $message);
                return;
            }

            // Check position capacity
            $position = Position::find($this->selectedPositionId);
            $currentApproved = $position->approvedCandidates()->count();

            if ($currentApproved >= $position->max_selections) {
                session()->flash('error', 'This position has reached maximum capacity.');
                return;
            }

            // Create candidate application
            // Determine user ID and status based on who is creating
            if ($this->isAdminFlow) {
                $userId = $this->selectedVoterId;
                $status = 'approved';
                $approvedBy = auth('admin')->id();
                $approvedAt = now();
            } else {
                $userId = auth('web')->id();
                $status = 'pending';
                $approvedBy = null;
                $approvedAt = null;
            }
            
            // Payment status logic
            if ($this->isAdminFlow) {
                switch ($this->adminPaymentAction) {
                    case 'waive':
                        $paymentStatus = 'waived';
                        break;
                    case 'manual':
                        $paymentStatus = 'paid';
                        break;
                    case 'require_proof':
                        $paymentStatus = $this->paymentProof ? 'submitted' : 'pending';
                        break;
                    default:
                        $paymentStatus = 'waived';
                }
            } else {
                $paymentStatus = $this->election->candidate_application_fee > 0 ? 'pending' : 'waived';
            }
            
            $applicationFee = $this->isAdminFlow && $this->adminPaymentAction === 'manual' 
                ? $this->adminPaymentAmount 
                : $this->election->candidate_application_fee;
                
            $candidate = Candidate::create([
                'user_id' => $userId,
                'election_id' => $this->election->id,
                'position_id' => $this->selectedPositionId,
                'manifesto' => $this->manifesto,
                'application_fee' => $applicationFee,
                'payment_status' => $paymentStatus,
                'payment_reference' => $this->paymentReference,
                'status' => $status,
                'approved_by' => $approvedBy,
                'approved_at' => $approvedAt,
            ]);
            
            // Create payment record for admin actions
            if ($this->isAdminFlow && in_array($this->adminPaymentAction, ['waive', 'manual'])) {
                \App\Models\Candidate\PaymentHistory::create([
                    'candidate_id' => $candidate->id,
                    'amount' => $applicationFee,
                    'status' => $paymentStatus,
                    'payment_method' => $this->adminPaymentAction === 'waive' ? 'waived' : 'admin_manual',
                    'reference' => $this->adminPaymentAction === 'waive' ? 'ADMIN_WAIVED' : 'ADMIN_MANUAL',
                    'notes' => $this->adminPaymentNotes,
                    'processed_by' => auth('admin')->id(),
                    'processed_at' => now(),
                ]);
            }

            // Upload and store documents
            $this->processDocumentUploads($candidate);
            
            // Process payment proof if provided
            if ($this->paymentProof) {
                $this->processPaymentProof($candidate);
            }

            // Update payment status if proof provided
            if ($this->paymentProof && $this->paymentReference) {
                $candidate->update(['payment_status' => 'submitted']);
            }

            DB::commit();

            // Send notification
            $notificationService = app(\App\Services\Candidate\CandidateNotificationService::class);
            $notificationService->notifyApplicationSubmitted($candidate);

            // Audit log the application submission
            app(\App\Services\Audit\AuditLogService::class)->log(
                'candidate_application_submitted',
                auth()->user(),
                \App\Models\Candidate\Candidate::class,
                $candidate->id,
                null,
                [
                    'election_id' => $this->election->id,
                    'position_id' => $this->selectedPositionId,
                    'manifesto_length' => strlen($this->manifesto),
                    'documents_uploaded' => array_keys(array_filter($this->uploadedDocuments)),
                    'application_fee' => $this->election->candidate_application_fee,
                ]
            );

            Log::info('Candidate application submitted successfully', [
                'candidate_id' => $candidate->id,
                'user_id' => auth()->id(),
                'election_id' => $this->election->id,
                'position_id' => $this->selectedPositionId,
            ]);

            // Delete draft after successful submission
            $userId = auth('web')->id() ?? auth('admin')->id();
            if ($userId) {
                \App\Models\Candidate\CandidateApplicationDraft::where('user_id', $userId)
                    ->where('election_id', $this->election->id)
                    ->delete();
            }

            session()->flash('success', 'Your application has been submitted successfully!');

            // Redirect based on user type
            if ($this->isAdminFlow) {
                return redirect()->route('admin.candidates.index')
                    ->with('success', 'Candidate application created successfully for ' . \App\Models\User::find($userId)->name);
            } else {
                return redirect()->route('candidate.application', $candidate->id);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Candidate application submission failed', [
                'user_id' => auth()->id(),
                'election_id' => $this->election->id,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Failed to submit application. Please try again.');
        } finally {
            $this->isSubmitting = false;
        }
    }

    private function processDocumentUploads(Candidate $candidate)
    {
        $documentTypes = [
            'cv' => 'CV/Resume',
            'certificates' => 'Certificates',
            'photo' => 'Profile Photo',
        ];

        $secureFileService = app(\App\Services\Security\SecureFileService::class);

        foreach ($this->uploadedDocuments as $type => $file) {
            if ($file) {
                $filename = $secureFileService->store($file);

                $document = CandidateDocument::create([
                    'candidate_id' => $candidate->id,
                    'document_type' => $type,
                    'file_path' => encrypt($filename),
                    'file_hash' => hash_file('sha256', $file->path()),
                    'status' => 'pending',
                ]);

                // Perform quick verification (non-blocking)
                $verificationService = app(\App\Services\DocumentVerificationService::class);
                $verificationService->quickVerify($document);

                // Dispatch background job for full verification
                \App\Jobs\ProcessDocumentVerification::dispatch($document)->onQueue('document-verification');
            }
        }
    }

    private function processPaymentProof(Candidate $candidate)
    {
        $secureFileService = app(\App\Services\Security\SecureFileService::class);
        $filename = $secureFileService->store($this->paymentProof);

        CandidateDocument::create([
            'candidate_id' => $candidate->id,
            'document_type' => 'payment_proof',
            'file_path' => encrypt($filename),
            'file_hash' => hash_file('sha256', $this->paymentProof->path()),
            'status' => 'pending',
        ]);
    }

    public function getCurrentPositionProperty()
    {
        if (!$this->selectedPositionId) {
            return null;
        }

        return collect($this->positions)->firstWhere('id', $this->selectedPositionId);
    }

    public function getCanProceedProperty()
    {
        return $this->progress['steps'][$this->currentStep] ?? false;
    }

    public function getRequiresPaymentProperty()
    {
        return $this->requiresPayment();
    }

    private function loadDraft()
    {
        if (!$this->election) return;
        
        $userId = auth('web')->id() ?? auth('admin')->id();
        if (!$userId) return;
        
        $cacheKey = "draft_save_{$this->election->id}_{$userId}";
        
        // Check cache first
        $cachedData = cache()->get($cacheKey);
        if ($cachedData) {
            $this->selectedElectionId = $cachedData['selectedElectionId'] ?? '';
            $this->selectedVoterId = $cachedData['selectedVoterId'] ?? '';
            $this->selectedPositionId = $cachedData['selectedPositionId'] ?? '';
            $this->manifesto = $cachedData['manifesto'] ?? '';
            $this->paymentReference = $cachedData['paymentReference'] ?? '';
            $this->acceptTerms = $cachedData['acceptTerms'] ?? false;
            $this->adminPaymentAction = $cachedData['adminPaymentAction'] ?? 'waive';
            $this->adminPaymentAmount = $cachedData['adminPaymentAmount'] ?? 0;
            $this->adminPaymentNotes = $cachedData['adminPaymentNotes'] ?? '';
            $this->currentStep = $cachedData['current_step'] ?? 1;
            return;
        }
        
        // Fallback to database
        $draft = \App\Models\Candidate\CandidateApplicationDraft::where('user_id', $userId)
            ->where('election_id', $this->election->id)
            ->first();

        if ($draft) {
            $data = $draft->form_data;
            $this->selectedElectionId = $data['selectedElectionId'] ?? '';
            $this->selectedVoterId = $data['selectedVoterId'] ?? '';
            $this->selectedPositionId = $data['selectedPositionId'] ?? '';
            $this->manifesto = $data['manifesto'] ?? '';
            $this->paymentReference = $data['paymentReference'] ?? '';
            $this->acceptTerms = $data['acceptTerms'] ?? false;
            $this->adminPaymentAction = $data['adminPaymentAction'] ?? 'waive';
            $this->adminPaymentAmount = $data['adminPaymentAmount'] ?? 0;
            $this->adminPaymentNotes = $data['adminPaymentNotes'] ?? '';
            $this->currentStep = $draft->current_step;
            
            // Load uploaded files
            if ($draft->uploaded_files) {
                \Log::info('Loading files from draft', ['files' => $draft->uploaded_files]);
                $this->loadUploadedFiles($draft->uploaded_files);
            }
        }
    }

    private function debouncedSave()
    {
        $userId = auth('web')->id() ?? auth('admin')->id();
        if (!$userId) return;
        
        $cacheKey = "draft_save_{$this->election->id}_{$userId}";
        
        // Store data in cache temporarily
        cache()->put($cacheKey, [
            'selectedElectionId' => $this->selectedElectionId,
            'selectedVoterId' => $this->selectedVoterId,
            'selectedPositionId' => $this->selectedPositionId,
            'manifesto' => $this->manifesto,
            'paymentReference' => $this->paymentReference,
            'acceptTerms' => $this->acceptTerms,
            'adminPaymentAction' => $this->adminPaymentAction,
            'adminPaymentAmount' => $this->adminPaymentAmount,
            'adminPaymentNotes' => $this->adminPaymentNotes,
            'current_step' => $this->currentStep,
        ], 300); // 5 minutes
        
        // Dispatch job to save after delay
        \App\Jobs\SaveApplicationDraft::dispatch(
            $userId,
            $this->election->id,
            $cacheKey
        )->delay(now()->addSeconds(3));
    }

    private function saveDraft()
    {
        $userId = auth('web')->id() ?? auth('admin')->id();
        if (!$userId) return;
        
        \App\Models\Candidate\CandidateApplicationDraft::updateOrCreate(
            [
                'user_id' => $userId,
                'election_id' => $this->election->id,
            ],
            [
                'form_data' => [
                    'selectedElectionId' => $this->selectedElectionId,
                    'selectedVoterId' => $this->selectedVoterId,
                    'selectedPositionId' => $this->selectedPositionId,
                    'manifesto' => $this->manifesto,
                    'paymentReference' => $this->paymentReference,
                    'acceptTerms' => $this->acceptTerms,
                    'adminPaymentAction' => $this->adminPaymentAction,
                    'adminPaymentAmount' => $this->adminPaymentAmount,
                    'adminPaymentNotes' => $this->adminPaymentNotes,
                ],
                'current_step' => $this->currentStep,
                'uploaded_files' => $this->getUploadedFilePaths(),
            ]
        );
    }

    private function saveUploadedFile($type, $file)
    {
        $userId = auth('web')->id() ?? auth('admin')->id();
        if (!$userId) return;
        
        $path = $file->store("drafts/{$userId}/{$this->election->id}", 'private');
        
        $draft = \App\Models\Candidate\CandidateApplicationDraft::where('user_id', $userId)
            ->where('election_id', $this->election->id)
            ->first();
            
        if ($draft) {
            $files = $draft->uploaded_files ?? [];
            $files[$type] = $path;
            $draft->update(['uploaded_files' => $files]);
        }
    }
    
    private function getUploadedFilePaths()
    {
        $userId = auth('web')->id() ?? auth('admin')->id();
        if (!$userId) return [];
        
        $draft = \App\Models\Candidate\CandidateApplicationDraft::where('user_id', $userId)
            ->where('election_id', $this->election->id)
            ->first();
            
        return $draft->uploaded_files ?? [];
    }
    
    private function loadUploadedFiles($filePaths)
    {
        foreach ($filePaths as $type => $path) {
            if ($path && \Storage::disk('private')->exists($path)) {
                try {
                    // Create a mock uploaded file for validation
                    $fullPath = \Storage::disk('private')->path($path);
                    $fileName = basename($path);
                    $mimeType = \Storage::disk('private')->mimeType($path);
                    
                    $this->uploadedDocuments[$type] = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        $fileName,
                        $mimeType,
                        null,
                        true // test mode
                    );
                    
                    \Log::info('Restored file from draft', [
                        'type' => $type,
                        'path' => $path,
                        'exists' => file_exists($fullPath)
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to restore file from draft', [
                        'type' => $type,
                        'path' => $path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.candidate.application-form');
    }
}