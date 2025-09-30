<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Enums\Election\ElectionType;
use App\Enums\Election\ElectionStatus;
use App\Enums\Election\ElectionPhase;
use Illuminate\Support\Facades\Log;

class CreateElection extends Component
{
    public $title = '';
    public $description = '';
    public $type = 'general';
    public $candidate_register_starts = '';
    public $candidate_register_ends = '';
    public $starts_at = '';
    public $ends_at = '';
    public $positions = [];

    public $autoSaveStatus = '';
    public $lastSaved;

    protected $rules = [
        'title' => 'required|min:3|max:255',
        'description' => 'required|min:10',
        'type' => 'required|in:general,bye,constitutional,opinion',
        'candidate_register_starts' => 'required|date|after:now',
        'candidate_register_ends' => 'required|date|after:candidate_register_starts',
        'starts_at' => 'required|date|after:candidate_register_ends',
        'ends_at' => 'required|date|after:starts_at',
        'positions' => 'required|array|min:1',
        'positions.*.title' => 'required|string|min:2|max:255',
        'positions.*.description' => 'nullable|string|max:1000',
        'positions.*.max_selections' => 'required|integer|min:1|max:50',
    ];

    public function addPosition()
    {
        $this->positions[] = [
            'title' => '',
            'description' => '',
            'max_selections' => 1,
        ];
    }

    public function removePosition($index)
    {
        if (isset($this->positions[$index])) {
            unset($this->positions[$index]);
            $this->positions = array_values($this->positions); // Reindex array
        }
    }

    public function save()
    {
        Log::info('CreateElection save method called', [
            'admin_id' => auth('admin')->id(),
            'form_data' => [
                'title' => $this->title,
                'type' => $this->type,
                'candidate_register_starts' => $this->candidate_register_starts,
                'candidate_register_ends' => $this->candidate_register_ends,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'positions_count' => count($this->positions),
            ]
        ]);

        try {
            $this->validate();
            Log::info('CreateElection validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('CreateElection validation failed', [
                'admin_id' => auth('admin')->id(),
                'errors' => $e->errors(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'candidate_register_starts' => $this->candidate_register_starts,
                    'candidate_register_ends' => $this->candidate_register_ends,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                    'positions' => $this->positions,
                ]
            ]);
            throw $e;
        }

        try {
            $election = Election::create([
                'title' => $this->title,
                'description' => $this->description,
                'type' => ElectionType::from($this->type),
                'status' => ElectionStatus::UPCOMING,
                'phase' => ElectionPhase::SETUP,
                'candidate_register_starts' => $this->candidate_register_starts,
                'candidate_register_ends' => $this->candidate_register_ends,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'created_by' => auth('admin')->id(),
            ]);

            Log::info('CreateElection election created successfully', [
                'election_id' => $election->id,
                'admin_id' => auth('admin')->id()
            ]);

            // Create positions
            foreach ($this->positions as $index => $positionData) {
                Position::create([
                    'election_id' => $election->id,
                    'title' => $positionData['title'],
                    'description' => $positionData['description'] ?? '',
                    'max_selections' => $positionData['max_selections'],
                    'order_index' => $index,
                    'is_active' => true,
                ]);
            }

            Log::info('CreateElection positions created successfully', [
                'election_id' => $election->id,
                'positions_count' => count($this->positions),
                'admin_id' => auth('admin')->id()
            ]);

            session()->flash('success', 'Election created successfully');
            return redirect()->route('admin.elections.show', $election->id);
        } catch (\Exception $e) {
            Log::error('CreateElection election creation failed', [
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'candidate_register_starts' => $this->candidate_register_starts,
                    'candidate_register_ends' => $this->candidate_register_ends,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                    'positions' => $this->positions,
                ]
            ]);
            session()->flash('error', 'Failed to create election: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.admin.create-election', [
            'electionTypes' => ElectionType::cases(),
        ]);
    }
}