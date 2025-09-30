<?php

namespace App\Http\Requests;

class VotingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && $this->user()->canVote();
    }

    public function rules(): array
    {
        return [
            'election_id' => [
                'required',
                'integer',
                'exists:elections,id',
                'election_active',
            ],
            'selections' => 'required|array|min:1',
            'selections.*.position_id' => [
                'required',
                'integer',
                'exists:positions,id',
                'distinct'
            ],
            'selections.*.candidate_ids' => [
                'required',
                'array',
                'min:1',
                'max:5' // Allow up to 5 candidates per position for ranked voting
            ],
            'selections.*.candidate_ids.*' => [
                'integer',
                'exists:candidates,id'
            ],
            'recaptcha_token' => 'required|string',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if election is active
            $election = \App\Models\Election\Election::find($this->election_id);
            if (!$election || !$election->isActive()) {
                $validator->errors()->add('election_id', 'Election is not currently active.');
            }

            // Check if user can vote in this election
            if (!$this->user()->canVoteInElection($election)) {
                $validator->errors()->add('election_id', 'You are not eligible to vote in this election.');
            }

            // Check if user has already voted
            if ($this->user()->hasVotedInElection($election)) {
                $validator->errors()->add('election_id', 'You have already voted in this election.');
            }

            // Validate position-candidate relationships
            if ($this->has('selections')) {
                foreach ($this->selections as $selection) {
                    $positionId = $selection['position_id'];
                    $candidateIds = $selection['candidate_ids'];

                    // Check if candidates belong to the position
                    $validCandidates = \App\Models\Candidate\Candidate::where('position_id', $positionId)
                        ->whereIn('id', $candidateIds)
                        ->where('status', 'approved')
                        ->count();

                    if ($validCandidates !== count($candidateIds)) {
                        $validator->errors()->add(
                            'selections',
                            "Invalid candidate selection for position {$positionId}."
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'selections.required' => 'Please make your voting selections.',
            'selections.array' => 'Voting selections must be properly formatted.',
            'selections.min' => 'You must vote for at least one position.',
            'selections.*.position_id.required' => 'Position selection is required.',
            'selections.*.candidate_ids.required' => 'Candidate selection is required for each position.',
            'selections.*.candidate_ids.array' => 'Candidate selection must be an array.',
            'selections.*.candidate_ids.min' => 'Select at least one candidate per position.',
            'selections.*.candidate_ids.max' => 'You can select up to 5 candidates per position.',
        ]);
    }
}