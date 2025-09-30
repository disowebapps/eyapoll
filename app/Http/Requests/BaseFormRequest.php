<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use App\Exceptions\ValidationException;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Handle failed validation by throwing custom exception
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException(
            'Validation failed',
            [
                'errors' => $validator->errors()->toArray(),
                'input' => $this->all(),
                'url' => $this->url(),
                'method' => $this->method(),
                'user_id' => $this->user() ? $this->user()->id : null,
            ]
        );
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'min' => [
                'string' => 'The :attribute must be at least :min characters.',
                'numeric' => 'The :attribute must be at least :min.',
            ],
            'max' => [
                'string' => 'The :attribute may not be greater than :max characters.',
                'numeric' => 'The :attribute may not be greater than :max.',
            ],
            'exists' => 'The selected :attribute is invalid.',
            'date' => 'The :attribute is not a valid date.',
            'before' => 'The :attribute must be a date before :date.',
            'after' => 'The :attribute must be a date after :date.',
            'regex' => 'The :attribute format is invalid.',
            'size' => 'The :attribute must be exactly :size characters.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'phone_number' => 'phone number',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'national_id' => 'national ID',
            'id_number' => 'ID number',
            'election_id' => 'election',
            'candidate_id' => 'candidate',
            'position_id' => 'position',
            'vote_token' => 'vote token',
            'receipt_hash' => 'receipt hash',
        ];
    }
}