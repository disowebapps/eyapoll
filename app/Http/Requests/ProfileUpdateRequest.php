<?php

namespace App\Http\Requests;

class ProfileUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        $rules = [
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                "unique:users,email,{$userId}",
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/'
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                "unique:users,phone_number,{$userId}",
                'regex:/^(\+234|234|0)[789][01]\d{8}$/'
            ],
        ];

        // Allow name updates only for certain user statuses
        if ($this->user()->can('updateName', $this->user())) {
            $rules['first_name'] = 'required|string|max:100|regex:/^[a-zA-Z\s\-]+$/';
            $rules['last_name'] = 'required|string|max:100|regex:/^[a-zA-Z\s\-]+$/';
        }

        return $rules;
    }

    public function prepareForValidation(): void
    {
        // Normalize phone number
        if ($this->has('phone_number')) {
            $this->merge([
                'phone_number' => app(\App\Services\Auth\VerificationService::class)
                    ->normalizePhoneNumber($this->phone_number)
            ]);
        }
    }
}