<?php

namespace App\Http\Requests;

class VoterRegistrationRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return !auth()->check(); // Only allow unauthenticated users
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z\s\-]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/'
            ],
            'phone_number' => [
                'required',
                'nigerian_phone',
                'unique:users,phone_number',
            ],
            'id_number' => [
                'required',
                'valid_id_number',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'voting_age',
            ],
            'password' => [
                'required',
                'string',
                'strong_password',
                'confirmed',
            ],
            'password_confirmation' => 'required|string',
            'recaptcha_token' => 'required|string',
        ];
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

        // Hash ID number for privacy
        if ($this->has('id_number')) {
            $cryptoService = app(\App\Services\Cryptographic\CryptographicService::class);
            $salt = $cryptoService->generateSalt();
            $hashedId = $cryptoService->hashIdNumber($this->id_number, $salt);

            $this->merge([
                'id_number_hash' => $hashedId,
                'id_salt' => $salt,
            ]);
        }
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'id_number.regex' => 'ID number must be exactly 11 digits.',
            'phone_number.regex' => 'Please enter a valid Nigerian phone number.',
        ]);
    }
}