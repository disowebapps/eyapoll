<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;
use App\Models\Observer;
use Illuminate\Auth\Access\HandlesAuthorization;

class ObserverPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the admin can view any observers.
     */
    public function viewAny($user): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermission('manage_users');
        }
        return false;
    }

    /**
     * Determine whether the admin can view the observer.
     */
    public function view($user, Observer $observer): bool
    {
        if ($user instanceof Admin) {
            return $user->hasPermission('manage_users');
        }
        return false;
    }


    /**
     * Determine whether the observer can view the observer dashboard.
     */
    public function viewObserverDashboard(Observer $observer): bool
    {
        return $observer->isActive();
    }

    /**
     * Determine whether the observer can view audit logs.
     */
    public function viewAuditLogs(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_audit_logs');
    }

    /**
     * Determine whether the observer can view election results.
     */
    public function viewElectionResults(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_election_results');
    }

    /**
     * Determine whether the observer can export audit logs.
     */
    public function exportAuditLogs(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('export_audit_logs');
    }

    /**
     * Determine whether the observer can view system health.
     */
    public function viewSystemHealth(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_system_health');
    }

    /**
     * Observer can NEVER create, update, or delete anything.
     * These methods always return false for strict read-only access.
     */
    public function create(Observer $observer): bool
    {
        return false;
    }

    public function update(Observer $observer, $model = null): bool
    {
        return false;
    }

    public function delete(Observer $observer, $model = null): bool
    {
        return false;
    }

    public function restore(Observer $observer, $model = null): bool
    {
        return false;
    }

    public function forceDelete(Observer $observer, $model = null): bool
    {
        return false;
    }

    /**
     * Observer can view published results but cannot modify elections.
     */
    public function viewElection(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_election_results');
    }

    public function modifyElection(Observer $observer): bool
    {
        return false;
    }

    /**
     * Observer can view user information but cannot modify users.
     */
    public function viewUser(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_user_activities');
    }

    public function modifyUser(Observer $observer): bool
    {
        return false;
    }

    /**
     * Observer can view notifications but cannot send or modify them.
     */
    public function viewNotifications(Observer $observer): bool
    {
        return $observer->isActive() && $observer->hasPrivilege('view_notifications');
    }

    public function sendNotifications(Observer $observer): bool
    {
        return false;
    }

    public function modifyNotifications(Observer $observer): bool
    {
        return false;
    }
}