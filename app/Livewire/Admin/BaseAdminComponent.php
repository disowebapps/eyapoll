<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class BaseAdminComponent extends Component
{
    use AuthorizesRequests;

    /**
     * Boot method to ensure admin authentication on all admin components
     */
    public function boot()
    {
        if (!auth('admin')->check()) {
            abort(401, 'Authentication required');
        }
    }

    /**
     * Override authorize to use admin guard with proper context
     */
    public function authorize($ability, $arguments = [])
    {
        $admin = auth('admin')->user();
        \Illuminate\Support\Facades\Log::info('BaseAdminComponent::authorize called', [
            'ability' => $ability,
            'arguments' => $arguments,
            'admin_id' => $admin ? $admin->id : null,
            'admin_email' => $admin ? $admin->email : null,
            'is_super_admin' => $admin ? $admin->is_super_admin : null,
        ]);

        if (!$admin) {
            \Illuminate\Support\Facades\Log::error('BaseAdminComponent::authorize - No admin user found');
            abort(401, 'Admin authentication required');
        }

        // Use Gate's authorize method with explicit user
        try {
            $result = app(\Illuminate\Contracts\Auth\Access\Gate::class)->forUser($admin)->authorize($ability, $arguments);
            \Illuminate\Support\Facades\Log::info('BaseAdminComponent::authorize - Authorization successful', [
                'ability' => $ability,
                'result' => $result,
            ]);
            return $result;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            \Illuminate\Support\Facades\Log::error('BaseAdminComponent::authorize - Authorization failed', [
                'ability' => $ability,
                'arguments' => $arguments,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}