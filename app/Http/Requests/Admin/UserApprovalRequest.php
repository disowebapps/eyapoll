<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Support\Facades\Auth;

class UserApprovalRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === \App\Enums\Auth\UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'action' => [
                'required',
                'string',
                'in:approve,reject,suspend,unsuspend',
            ],
            'reason' => [
                'required_if:action,reject,suspend',
                'nullable',
                'string',
                'max:1000',
            ],
            'review_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = \App\Models\User::find($this->user_id);

            if (!$user) {
                return; // Let the exists rule handle this
            }

            $action = $this->action;

            // Validate action based on current user status
            switch ($action) {
                case 'approve':
                    if (!in_array($user->status, ['pending', 'review'])) {
                        $validator->errors()->add('action', 'User is not in a pending or review status.');
                    }
                    break;

                case 'reject':
                    if (!in_array($user->status, ['pending', 'review'])) {
                        $validator->errors()->add('action', 'User is not in a pending or review status.');
                    }
                    break;

                case 'suspend':
                    if ($user->status !== 'approved') {
                        $validator->errors()->add('action', 'Only approved users can be suspended.');
                    }
                    break;

                case 'unsuspend':
                    if ($user->status !== 'suspended') {
                        $validator->errors()->add('action', 'User is not currently suspended.');
                    }
                    break;
            }
        });
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'action.in' => 'Invalid action specified.',
            'reason.required_if' => 'A reason is required when rejecting or suspending a user.',
        ]);
    }
}