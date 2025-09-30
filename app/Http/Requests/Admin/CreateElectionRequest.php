<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;

class CreateElectionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && $this->user()->role === \App\Enums\Auth\UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'unique:elections,title',
            ],
            'description' => [
                'required',
                'string',
                'max:2000',
            ],
            'type' => [
                'required',
                'string',
                'in:general,presidential,gubernatorial,local',
            ],
            'start_date' => [
                'required',
                'date',
                'after:now',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'positions' => 'required|array|min:1',
            'positions.*.title' => [
                'required',
                'string',
                'max:255',
            ],
            'positions.*.description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'positions.*.max_candidates' => [
                'required',
                'integer',
                'min:1',
                'max:50',
            ],
            'positions.*.voting_type' => [
                'required',
                'string',
                'in:first_past_post,ranked_choice,approval',
            ],
            'eligibility_criteria' => 'nullable|array',
            'eligibility_criteria.min_age' => 'nullable|integer|min:18|max:120',
            'eligibility_criteria.max_age' => 'nullable|integer|min:18|max:120|gte:eligibility_criteria.min_age',
            'eligibility_criteria.required_documents' => 'nullable|array',
            'eligibility_criteria.required_documents.*' => 'string|in:national_id,passport,drivers_license',
            'settings' => 'nullable|array',
            'settings.allow_observers' => 'boolean',
            'settings.require_verification' => 'boolean',
            'settings.public_results' => 'boolean',
            'settings.anonymize_votes' => 'boolean',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $startDate = $this->start_date;
            $endDate = $this->end_date;

            if ($startDate && $endDate) {
                $duration = \Carbon\Carbon::parse($startDate)->diffInHours(\Carbon\Carbon::parse($endDate));

                if ($duration < 24) {
                    $validator->errors()->add('end_date', 'Election must run for at least 24 hours.');
                }

                if ($duration > 720) { // 30 days
                    $validator->errors()->add('end_date', 'Election cannot run for more than 30 days.');
                }
            }

            // Check for overlapping elections
            if ($startDate && $endDate) {
                $overlapping = \App\Models\Election\Election::where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })->exists();

                if ($overlapping) {
                    $validator->errors()->add('start_date', 'Election dates conflict with an existing election.');
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'title.unique' => 'An election with this title already exists.',
            'type.in' => 'Invalid election type selected.',
            'positions.required' => 'At least one position must be defined.',
            'positions.*.max_candidates.min' => 'Each position must allow at least 1 candidate.',
            'positions.*.max_candidates.max' => 'Each position cannot allow more than 50 candidates.',
            'positions.*.voting_type.in' => 'Invalid voting type selected.',
            'eligibility_criteria.required_documents.*.in' => 'Invalid document type for eligibility.',
        ]);
    }
}