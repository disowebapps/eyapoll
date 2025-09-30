<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateObserverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('admin')->check();
    }

    public function rules(): array
    {
        return [
            'access_level' => 'required|string|in:read_only,full_access',
            'status' => 'required|string|in:active,suspended,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'access_level.required' => 'Access level is required',
            'access_level.in' => 'Invalid access level selected',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
        ];
    }
}