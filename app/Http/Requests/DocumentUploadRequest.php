<?php

namespace App\Http\Requests;

class DocumentUploadRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpeg,jpg,png,pdf',
            ],
            'document_type' => [
                'required',
                'string',
                'in:national_id,passport,drivers_license,birth_certificate',
            ],
            'expiry_date' => [
                'nullable',
                'date',
                'after:today',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('document');
            if ($file) {
                // Use the verification service to validate the file
                $errors = app(\App\Services\Auth\VerificationService::class)
                    ->validateDocumentFile($file);

                foreach ($errors as $error) {
                    $validator->errors()->add('document', $error);
                }
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'document.max' => 'Document file size must not exceed 10MB.',
            'document.mimes' => 'Document must be a JPEG, PNG, or PDF file.',
            'document_type.in' => 'Invalid document type selected.',
            'expiry_date.after' => 'Document expiry date must be in the future.',
        ]);
    }
}