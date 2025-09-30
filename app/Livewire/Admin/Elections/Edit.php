<?php

namespace App\Livewire\Admin\Elections;

use App\Models\Election\Election;
use App\Models\Election\Position;
use App\Enums\Election\ElectionType;
use App\Livewire\Admin\BaseAdminComponent;
use Illuminate\Support\Facades\Log;

class Edit extends BaseAdminComponent
{

    public Election $election;
    public $title;
    public $description;
    public $type;
    public $starts_at;
    public $ends_at;
    public $candidate_register_starts;
    public $candidate_register_ends;
    public $registration_resumed_at;
    public $positions = [];

    protected function rules()
    {
        $electionTypes = implode(',', array_column(ElectionType::cases(), 'value'));
        
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'type' => "required|in:{$electionTypes}",
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'candidate_register_starts' => 'nullable|date',
            'candidate_register_ends' => 'nullable|date|after:candidate_register_starts',
            'registration_resumed_at' => 'nullable|date',
            'positions.*.title' => 'required|string|max:255',
            'positions.*.description' => 'nullable|string|max:500',
            'positions.*.max_candidates' => 'required|integer|min:1|max:50',
        ];
    }

    public function mount($electionId)
    {
        $admin = auth('admin')->user();

        Log::info('Election edit form loading', [
            'election_id' => $electionId,
            'admin_id' => $admin?->id,
            'admin_email' => $admin?->email
        ]);

        try {
            $this->loadElection($electionId, $admin);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Election not found for editing', [
                'election_id' => $electionId,
                'admin_id' => $admin?->id,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Election not found');
        } catch (\Exception $e) {
            Log::error('Error loading election edit form', [
                'election_id' => $electionId,
                'admin_id' => $admin?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Unable to load election for editing.');
            return redirect()->route('admin.elections.index');
        }
    }

    private function loadElection($electionId, $admin)
    {
        $this->election = Election::with('positions')->findOrFail($electionId);

        Log::info('Election found for editing', [
            'election_id' => $this->election->id,
            'election_title' => $this->election->title,
            'election_status' => $this->election->status->value,
            'can_be_edited' => $this->election->canBeEdited(),
            'positions_count' => $this->election->positions->count()
        ]);

        $this->authorize('update', $this->election);

        if (!$this->election->canBeEdited()) {
            Log::warning('Attempted to edit non-editable election', [
                'election_id' => $this->election->id,
                'election_status' => $this->election->status->value,
                'admin_id' => $admin?->id
            ]);
            session()->flash('error', 'This election cannot be edited.');
            return redirect()->route('admin.elections.show', $this->election->id);
        }

        $this->fill([
            'title' => $this->election->title,
            'description' => $this->election->description,
            'type' => $this->election->type->value,
            'starts_at' => $this->election->starts_at->format('Y-m-d\TH:i'),
            'ends_at' => $this->election->ends_at->format('Y-m-d\TH:i'),
            'candidate_register_starts' => $this->election->candidate_register_starts?->format('Y-m-d\TH:i'),
            'candidate_register_ends' => $this->election->candidate_register_ends?->format('Y-m-d\TH:i'),
            'registration_resumed_at' => $this->election->registration_resumed_at?->format('Y-m-d\TH:i'),
        ]);

        $this->positions = $this->election->positions->map(fn($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'description' => $p->description,
            'max_candidates' => $p->max_candidates ?? 1,
            'order_index' => $p->order_index,
        ])->toArray();

        Log::info('Election edit form loaded successfully', [
            'election_id' => $this->election->id,
            'admin_id' => $admin?->id,
            'form_data' => [
                'title' => $this->title,
                'type' => $this->type,
                'positions_count' => count($this->positions),
            ]
        ]);
    }

    public function addPosition()
    {
        $admin = auth('admin')->user();
        $currentCount = count($this->positions);

        $newPosition = [
            'id' => null,
            'title' => '',
            'description' => '',
            'max_candidates' => 1,
            'order_index' => $currentCount
        ];

        $this->positions[] = $newPosition;
        $newCount = $currentCount + 1;

        Log::info('Position added to election edit form', [
            'election_id' => $this->election->id,
            'admin_id' => $admin?->id,
            'position_index' => $currentCount,
            'total_positions' => $newCount
        ]);
    }

    public function removePosition($index)
    {
        if (!array_key_exists($index, $this->positions)) {
            return;
        }
        
        $admin = auth('admin')->user();

        try {
            Log::info('Position removal initiated', [
                'election_id' => $this->election?->id,
                'admin_id' => $admin?->id,
                'position_index' => $index,
                'position_data' => $this->positions[$index] ?? null
            ]);
        } catch (\Exception $e) {
            // Continue execution even if logging fails
        }

        if (isset($this->positions[$index]['id']) && $this->positions[$index]['id']) {
            $positionId = (int) $this->positions[$index]['id'];
            
            try {
                $position = Position::find($positionId);

                if ($position) {
                    $position->delete();
                    Log::info('Position deleted from database', [
                        'election_id' => $this->election->id,
                        'position_id' => $positionId,
                        'position_title' => $position->title
                    ]);
                } else {
                    Log::warning('Position not found in database for deletion', [
                        'election_id' => $this->election->id,
                        'position_id' => $positionId
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to delete position from database', [
                    'election_id' => $this->election->id,
                    'position_id' => $positionId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        unset($this->positions[$index]);
        $this->positions = array_values($this->positions);

        Log::info('Position removed from edit form', [
            'election_id' => $this->election->id,
            'admin_id' => $admin?->id,
            'remaining_positions' => count($this->positions)
        ]);
    }

    public function save()
    {
        $admin = auth('admin')->user();

        Log::info('Election update initiated', [
            'election_id' => $this->election->id,
            'admin_id' => $admin?->id,
            'election_title' => $this->election->title,
            'form_data' => [
                'title' => $this->title,
                'type' => $this->type,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'positions_count' => count($this->positions),
            ]
        ]);

        try {
            // Validate form data
            $validatedData = $this->validate();
            Log::info('Election update validation passed', [
                'election_id' => $this->election->id,
                'validated_data' => $validatedData
            ]);

            // Update election
            $oldData = [
                'title' => $this->election->title,
                'description' => $this->election->description,
                'type' => $this->election->type->value,
                'starts_at' => $this->election->starts_at->toISOString(),
                'ends_at' => $this->election->ends_at->toISOString(),
            ];

            $newData = [
                'title' => $this->title,
                'description' => $this->description,
                'type' => $this->type,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
            ];

            $this->election->update([
                'title' => $this->title,
                'description' => $this->description,
                'type' => ElectionType::from($this->type),
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
                'candidate_register_starts' => $this->candidate_register_starts,
                'candidate_register_ends' => $this->candidate_register_ends,
                'registration_resumed_at' => $this->registration_resumed_at,
            ]);

            // Generate audit log for election update
            try {
                app(\App\Services\Audit\AuditLogService::class)->log(
                    'election_updated',
                    $admin,
                    \App\Models\Election\Election::class,
                    $this->election->id,
                    $oldData,
                    $newData
                );
            } catch (\Exception $e) {
                Log::error('Failed to create audit log for election update', [
                    'election_id' => $this->election->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Election basic data updated', [
                'election_id' => $this->election->id,
                'old_data' => $oldData,
                'new_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                ]
            ]);

            // Update positions
            $positionsUpdated = 0;
            $positionsCreated = 0;

            foreach ($this->positions as $index => $position) {
                try {
                    if (isset($position['id']) && $position['id']) {
                        // Update existing position
                        $positionId = (int) $position['id'];
                        if ($positionId <= 0) {
                            continue;
                        }
                        $existingPosition = Position::whereRaw('id = ? AND election_id = ?', [$positionId, $this->election->id])
                            ->first();
                        if ($existingPosition) {
                            $existingPosition->title = (string) $position['title'];
                            $existingPosition->description = (string) ($position['description'] ?? '');
                            $existingPosition->max_candidates = (int) $position['max_candidates'];
                            $existingPosition->order_index = (int) $index;
                            $existingPosition->save();
                            $positionsUpdated++;
                            Log::debug('Position updated', [
                                'position_id' => $position['id'],
                                'title' => $position['title']
                            ]);
                        } else {
                            Log::warning('Position not found for update', [
                                'position_id' => $position['id'],
                                'election_id' => $this->election->id
                            ]);
                        }
                    } else {
                        // Create new position
                        Position::create([
                            'election_id' => $this->election->id,
                            'title' => (string) $position['title'],
                            'description' => (string) ($position['description'] ?? ''),
                            'max_candidates' => (int) $position['max_candidates'],
                            'order_index' => (int) $index,
                        ]);
                        $positionsCreated++;
                        Log::debug('Position created', [
                            'election_id' => $this->election->id,
                            'title' => $position['title']
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to save position', [
                        'election_id' => $this->election->id,
                        'position_index' => $index,
                        'position_data' => $position,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Election update completed successfully', [
                'election_id' => $this->election->id,
                'admin_id' => $admin?->id,
                'positions_updated' => $positionsUpdated,
                'positions_created' => $positionsCreated,
                'total_positions' => count($this->positions)
            ]);

            session()->flash('success', 'Election updated successfully.');
            return redirect()->route('admin.elections.show', $this->election->id);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Election update validation failed', [
                'election_id' => $this->election->id,
                'admin_id' => $admin?->id,
                'errors' => $e->errors(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                ]
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Election update failed', [
                'election_id' => $this->election->id,
                'admin_id' => $admin?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'form_data' => [
                    'title' => $this->title,
                    'type' => $this->type,
                    'starts_at' => $this->starts_at,
                    'ends_at' => $this->ends_at,
                ]
            ]);

            session()->flash('error', 'Failed to update election: ' . $e->getMessage());
            return;
        }
    }

    public function updated($propertyName)
    {
        // Ensure max_candidates has default values when positions are updated
        if (str_starts_with($propertyName, 'positions.') && str_ends_with($propertyName, '.title')) {
            $parts = explode('.', $propertyName);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $index = (int) $parts[1];
                if (isset($this->positions[$index]) && (!isset($this->positions[$index]['max_candidates']) || empty($this->positions[$index]['max_candidates']))) {
                    $this->positions[$index]['max_candidates'] = 1;
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.admin.elections.edit', [
            'electionTypes' => ElectionType::cases()
        ]);
    }
}