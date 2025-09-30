<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;

class CandidateRegistrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && $this->user()->role === \App\Enums\Auth\UserRole::VOTER;
    }

    public function rules(): array
    {
        return [
            'election_id' => [
                'required',
                'integer',
                'exists:elections,id',
            ],
            'position_id' => [
                'required',
                'integer',
                'exists:positions,id',
            ],
            'manifesto' => [
                'required',
                'string',
                'max:5000',
            ],
            'qualifications' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'experience' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'platform' => [
                'nullable',
                'array',
            ],
            'platform.*' => [
                'string',
                'max:500',
            ],
            'documents' => [
                'nullable',
                'array',
                'max:5',
            ],
            'documents.*' => [
                'file',
                'max:5120', // 5MB
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $election = \App\Models\Election\Election::find($this->election_id);
            $position = \App\Models\Election\Position::find($this->position_id);

            // Check if election exists and is accepting candidates
            if (!$election || !$election->isAcceptingCandidates()) {
                $validator->errors()->add('election_id', 'Election is not accepting candidate registrations.');
            }

            // Check if position belongs to the election
            if ($position && $position->election_id !== $this->election_id) {
                $validator->errors()->add('position_id', 'Position does not belong to the selected election.');
            }

            // Check if user already applied for this position
            $existingApplication = \App\Models\Candidate\Candidate::where('user_id', $this->user()->id)
                ->where('election_id', $this->election_id)
                ->where('position_id', $this->position_id)
                ->exists();

            if ($existingApplication) {
                $validator->errors()->add('position_id', 'You have already applied for this position.');
            }

            // Check position capacity
            if ($position) {
                $currentCandidates = $position->candidates()->where('status', 'approved')->count();
                if ($currentCandidates >= $position->max_candidates) {
                    $validator->errors()->add('position_id', 'This position has reached its maximum number of candidates.');
                }
            }

            // Validate documents if provided
            if ($this->hasFile('documents')) {
                foreach ($this->file('documents') as $document) {
                    $errors = app(\App\Services\Auth\VerificationService::class)
                        ->validateDocumentFile($document);

                    if (!empty($errors)) {
                        $validator->errors()->add('documents', 'One or more documents failed validation: ' . implode(', ', $errors));
                        break;
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'manifesto.required' => 'A manifesto is required for candidate registration.',
            'manifesto.max' => 'Manifesto cannot exceed 5000 characters.',
            'documents.max' => 'You can upload a maximum of 5 documents.',
            'documents.*.max' => 'Each document must not exceed 5MB.',
            'documents.*.mimes' => 'Documents must be PDF, DOC, DOCX, JPG, JPEG, or PNG files.',
        ]);
    }
}