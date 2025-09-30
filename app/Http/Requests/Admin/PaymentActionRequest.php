<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PaymentActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|min:10|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A reason is required for this payment action.',
            'reason.min' => 'Reason must be at least 10 characters.',
            'reason.max' => 'Reason cannot exceed 500 characters.'
        ];
    }
}