<?php

namespace App\Livewire\Admin\Elections;

use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Enums\Election\ElectionPhase;
use App\Livewire\Admin\BaseAdminComponent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Create extends BaseAdminComponent
{

    public $title = '';
    public $description = '';
    public $type = '';
    public $candidate_register_starts = '';
    public $candidate_register_ends = '';
    public $starts_at = '';
    public $ends_at = '';
    public $positions = [];
    public $autoSaveStatus = '';
    public $lastSaved = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string|max:1000',
        'type' => 'required|in:general,bye,constitutional,opinion',
        'candidate_register_starts' => 'nullable|date|after:now',
        'candidate_register_ends' => 'nullable|date|after:candidate_register_starts',
        'starts_at' => 'required|date|after:now',
        'ends_at' => 'required|date|after:starts_at',
        'positions.*.title' => 'required|string|max:255',
        'positions.*.description' => 'nullable|string|max:500',
        'positions.*.max_selections' => 'required|integer|min:1|max:50',
        'positions.*.application_fee' => 'nullable|numeric|min:0|max:999999.99',
    ];

    public function mount()
    {
        $this->authorize('create', Election::class);
        $this->loadDraft();
        $this->addPosition();
    }

    public function addPosition()
    {
        $newPosition = [
            'title' => '',
            'description' => '',
            'max_selections' => 1,
            'application_fee' => 0,
            'order_index' => count($this->positions)
        ];
        $this->positions[] = $newPosition;
        Log::info('Position added to election form', [
            'admin_id' => auth('admin')->id(),
            'new_position_index' => count($this->positions) - 1,
            'total_positions' => count($this->positions)
        ]);
    }

    public function removePosition($index)
    {
        unset($this->positions[$index]);
        $this->positions = array_values($this->positions);
        Log::info('Position removed from election form', [
            'admin_id' => auth('admin')->id(),
            'removed_index' => $index,
            'total_positions' => count($this->positions)
        ]);
        $this->autoSave();
    }

    public function save()
    {
        Log::info('Elections/Create save method called');
        $admin = auth('admin')->user();

        Log::info('Elections/Create save initiated', [
            'admin_id' => $admin?->id,
            'admin_email' => $admin?->email,
            'form_data' => [
                'title' => $this->title,
                'type' => $this->type,
                'candidate_register_starts' => $this->candidate_register_starts,
                'candidate_register_ends' => $this->candidate_register_ends,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'positions_count' => count($this->positions),
                'positions' => $this->positions,
            ]
        ]);

        try {
            Log::info('Elections/Create about to validate', [
                'admin_id' => $admin?->id,
                'raw_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'candidate_register_starts' => $this->candidate_register_starts,
                    'candidate_register_ends' => $this->candidate_register_ends,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                    'positions' => $this->positions,
                ]
            ]);
            // Validate form data
            $validatedData = $this->validate();
            Log::info('Elections/Create validation passed', [
                'admin_id' => $admin?->id,
                'validated_data' => $validatedData
            ]);

            $election = Election::create([
                'title' => $this->title,
                'description' => $this->description,
                'type' => ElectionType::from($this->type),
                'status' => ElectionStatus::UPCOMING,
                'phase' => ElectionPhase::SETUP,
                'candidate_register_starts' => $this->candidate_register_starts ?: null,
                'candidate_register_ends' => $this->candidate_register_ends ?: null,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'created_by' => auth('admin')->id(),
            ]);

            Log::info('Election created successfully', [
                'election_id' => $election->id,
                'election_title' => $election->title,
                'admin_id' => $admin?->id
            ]);

            $positionsCreated = 0;
            foreach ($this->positions as $index => $position) {
                try {
                    Log::info('Creating election position', [
                        'election_id' => $election->id,
                        'position_index' => $index,
                        'position_title' => $position['title'],
                        'admin_id' => $admin?->id
                    ]);

                    $createdPosition = Position::create([
                        'election_id' => $election->id,
                        'title' => $position['title'],
                        'description' => $position['description'],
                        'max_selections' => $position['max_selections'],
                        'application_fee' => $position['application_fee'] ?? 0,
                        'order_index' => $index,
                    ]);

                    $positionsCreated++;

                    Log::info('Election position created successfully', [
                        'election_id' => $election->id,
                        'position_id' => $createdPosition->id,
                        'position_index' => $index,
                        'admin_id' => $admin?->id
                    ]);

                } catch (\Exception $e) {
                    Log::error('Election position creation failed', [
                        'election_id' => $election->id,
                        'position_index' => $index,
                        'position_data' => $position,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'admin_id' => $admin?->id
                    ]);
                    throw $e;
                }
            }

            Log::info('Election positions created', [
                'election_id' => $election->id,
                'positions_created' => $positionsCreated,
                'admin_id' => $admin?->id
            ]);

            // Clear draft after successful save
            $this->clearDraft();

            session()->flash('success', 'Election created successfully.');
            return redirect()->route('admin.elections.show', $election->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Election creation validation failed', [
                'admin_id' => $admin?->id,
                'errors' => $e->errors(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'candidate_register_starts' => $this->candidate_register_starts,
                    'candidate_register_ends' => $this->candidate_register_ends,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                ]
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Election creation failed', [
                'admin_id' => $admin?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'candidate_register_starts' => $this->candidate_register_starts,
                    'candidate_register_ends' => $this->candidate_register_ends,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                ]
            ]);

            session()->flash('error', 'Failed to create election: ' . $e->getMessage());
            return;
        }
    }

    public function autoSave()
    {
        $admin = auth('admin')->user();

        try {
            $draftData = [
                'title' => $this->title,
                'description' => $this->description,
                'type' => $this->type,
                'candidate_register_starts' => $this->candidate_register_starts,
                'candidate_register_ends' => $this->candidate_register_ends,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'positions' => $this->positions,
                'saved_at' => now()->toISOString(),
            ];

            Cache::put("election_draft_" . auth('admin')->id(), $draftData, now()->addHours(24));
            $this->lastSaved = now();
            $this->autoSaveStatus = 'Draft saved';

            Log::debug('Election draft auto-saved', [
                'admin_id' => $admin?->id,
                'draft_size' => strlen(json_encode($draftData)),
                'positions_count' => count($this->positions)
            ]);
        } catch (\Exception $e) {
            $this->autoSaveStatus = 'Auto-save failed';

            Log::warning('Election draft auto-save failed', [
                'admin_id' => $admin?->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function loadDraft()
    {
        $admin = auth('admin')->user();
        $draft = Cache::get("election_draft_" . auth('admin')->id());

        if ($draft) {
            $this->title = $draft['title'] ?? '';
            $this->description = $draft['description'] ?? '';
            $this->type = $draft['type'] ?? '';
            $this->candidate_register_starts = $draft['candidate_register_starts'] ?? '';
            $this->candidate_register_ends = $draft['candidate_register_ends'] ?? '';
            $this->starts_at = $draft['starts_at'] ?? '';
            $this->ends_at = $draft['ends_at'] ?? '';
            $this->positions = $draft['positions'] ?? [];
            // Migrate old max_candidates to max_selections if needed
            foreach ($this->positions as &$position) {
                if (isset($position['max_candidates']) && !isset($position['max_selections'])) {
                    $position['max_selections'] = $position['max_candidates'];
                    unset($position['max_candidates']);
                }
            }
            $this->lastSaved = isset($draft['saved_at']) ? \Carbon\Carbon::parse($draft['saved_at']) : null;
            $this->autoSaveStatus = 'Draft loaded';

            Log::info('Election draft loaded from cache', [
                'admin_id' => $admin?->id,
                'draft_age_seconds' => $this->lastSaved ? now()->diffInSeconds($this->lastSaved) : null,
                'positions_count' => count($this->positions)
            ]);
        } else {
            Log::debug('No election draft found in cache', [
                'admin_id' => $admin?->id
            ]);
        }
    }

    public function clearDraft()
    {
        Cache::forget("election_draft_" . auth('admin')->id());
        $this->autoSaveStatus = '';
        $this->lastSaved = null;
    }

    public function updated($property)
    {
        // Auto-save when any property changes
        $this->autoSave();
    }

    public function render()
    {
        return view('livewire.admin.elections.create', [
            'electionTypes' => ElectionType::cases()
        ]);
    }
}