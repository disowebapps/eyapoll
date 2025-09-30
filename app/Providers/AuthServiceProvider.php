<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Admin;
use App\Models\Election\Election;
use App\Models\Candidate\Candidate;
use App\Models\Observer;
use App\Policies\AdminPolicy;
use App\Policies\ElectionPolicy;
use App\Policies\CandidatePolicy;
use App\Policies\ObserverPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Admin::class => AdminPolicy::class,
        Election::class => ElectionPolicy::class,
        Candidate::class => CandidatePolicy::class,
        Observer::class => ObserverPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Define admin permission gates
        Gate::define('manage_users', function ($admin) {
            return $admin->hasPermission('manage_users');
        });

        Gate::define('suspend_users', function ($admin) {
            return $admin->hasPermission('suspend_users');
        });

        Gate::define('issue-token', function ($admin, $user = null, $election = null) {
            return $admin->hasPermission('manage_users');
        });

        Gate::define('revoke-token', function ($admin, $token = null) {
            return $admin->hasPermission('manage_users');
        });

        Gate::define('reassign-token', function ($admin, $token = null, $newUser = null) {
            return $admin->hasPermission('manage_users');
        });

        Gate::define('bulk-issue-tokens', function ($admin, $election = null) {
            return $admin->hasPermission('manage_users');
        });

        // Ensure policies work with admin guard for admin routes
        Gate::before(function ($user, $ability) {
            // Only apply to admin routes
            if (!request()->is('admin/*') && !request()->is('livewire/message/*')) {
                return null;
            }

            // If no user but admin is authenticated, use admin user
            if (!$user && auth('admin')->check()) {
                return Gate::forUser(auth('admin')->user())->check($ability, func_get_args()[2] ?? []);
            }

            return null;
        });
    }
}