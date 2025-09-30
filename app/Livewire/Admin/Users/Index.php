<?php

namespace App\Livewire\Admin\Users;

use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Livewire\Admin\BaseAdminComponent;
use App\Models\User;
use App\Models\Candidate\Candidate;
use App\Models\Observer;
use App\Models\Admin;

class Index extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $typeFilter = 'all';

    protected $queryString = ['search', 'statusFilter', 'typeFilter'];

    public function mount()
    {
        // Check if admin has permission to manage users
        $admin = auth('admin')->user();
        if (!$admin || (!$admin->is_super_admin && !$admin->hasPermission('manage_users'))) {
            abort(403, 'This action is unauthorized.');
        }
        
        $status = request('status');
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'suspended'])) {
            $this->statusFilter = $status;
        }
        
        $type = request('type');
        if ($type && in_array($type, ['voter', 'candidate', 'observer', 'admin'])) {
            $this->typeFilter = $type;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public $selectedUser = null;
    public $showUserModal = false;
    public $showAddUserModal = false;
    public $showConfirmModal = false;
    public $confirmAction = null;
    public $confirmUserId = null;
    public $confirmUserType = null;
    public $confirmUserName = null;
    public $suspensionReason = '';
    public $showPasswordModal = false;
    public $passwordUserId = null;
    public $passwordUserType = null;
    public $newPassword = '';
    public $confirmPassword = '';
    public $newUser = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone_number' => '',
        'type' => 'voter',
        'password' => ''
    ];

    public function viewUser($userId, $type)
    {
        return redirect()->route('admin.users.show', $userId);
    }
    
    public function closeUserModal()
    {
        $this->showUserModal = false;
        $this->selectedUser = null;
    }

    public function approveUser($userId, $type)
    {
        $this->confirmAction = 'approve';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }

    public function rejectUser($userId, $type)
    {
        $this->confirmAction = 'reject';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }

    public function resetFilters()
    {
        Log::info('resetFilters called');
        $this->search = '';
        $this->statusFilter = 'all';
        $this->typeFilter = 'all';
    }

    public function exportUsers()
    {
        Log::info('exportUsers called');
        session()->flash('info', 'Export functionality will be implemented soon.');
    }
    
    public function addUser()
    {
        return redirect()->route('admin.users.create');
    }
    
    public function resetNewUserForm()
    {
        $this->newUser = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone_number' => '',
            'type' => 'voter',
            'password' => ''
        ];
    }
    
    public function closeAddUserModal()
    {
        $this->showAddUserModal = false;
        $this->resetNewUserForm();
    }
    
    public function suspendUser($userId, $type)
    {
        if (!Gate::allows('suspend_users')) {
            session()->flash('error', 'You do not have permission to suspend users.');
            return;
        }

        $admin = auth('admin')->user();

        // Prevent super admin from suspending themselves
        if ($type === 'admin' && $userId == $admin->id) {
            session()->flash('error', 'You cannot suspend yourself.');
            return;
        }
        
        $this->confirmAction = 'suspend';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->suspensionReason = '';
        $this->showConfirmModal = true;
    }
    
    public function unsuspendUser($userId, $type)
    {
        if (!Gate::allows('suspend_users')) {
            session()->flash('error', 'You do not have permission to unsuspend users.');
            return;
        }
        
        $this->confirmAction = 'unsuspend';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }
    
    public function reviewUser($userId, $type)
    {
        if (!Gate::allows('manage_users')) {
            session()->flash('error', 'You do not have permission to review users.');
            return;
        }

        $this->confirmAction = 'review';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }

    public function holdUser($userId, $type)
    {
        if (!Gate::allows('manage_users')) {
            session()->flash('error', 'You do not have permission to hold users.');
            return;
        }

        $this->confirmAction = 'hold';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }

    public function expireUser($userId, $type)
    {
        if (!Gate::allows('manage_users')) {
            session()->flash('error', 'You do not have permission to expire users.');
            return;
        }

        $this->confirmAction = 'expire';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }

    public function requireRenewal($userId, $type)
    {
        if (!Gate::allows('manage_users')) {
            session()->flash('error', 'You do not have permission to require renewals.');
            return;
        }

        $this->confirmAction = 'require_renewal';
        $this->confirmUserId = $userId;
        $this->confirmUserType = $type;
        $this->confirmUserName = $this->getUserName($userId, $type);
        $this->showConfirmModal = true;
    }
    
    public function getUserName($userId, $type)
    {
        $user = match($type) {
            'candidate' => Candidate::with('user')->find($userId)?->user,
            'voter' => User::find($userId),
            'observer' => Observer::find($userId),
            'admin' => Admin::find($userId),
            default => User::find($userId)
        };
        
        return $user ? $user->first_name . ' ' . $user->last_name : 'Unknown User';
    }
    
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmAction = null;
        $this->confirmUserId = null;
        $this->confirmUserType = null;
        $this->confirmUserName = null;
        $this->suspensionReason = '';
        $this->resetErrorBag();
    }

    public function updatePassword($userId, $type)
    {
        $this->passwordUserId = $userId;
        $this->passwordUserType = $type;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->showPasswordModal = true;
    }

    public function closePasswordModal()
    {
        $this->showPasswordModal = false;
        $this->passwordUserId = null;
        $this->passwordUserType = null;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->resetErrorBag();
    }

    public function savePassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:8',
            'confirmPassword' => 'required|same:newPassword'
        ]);

        $table = match($this->passwordUserType) {
            'voter' => 'users',
            'candidate' => 'candidates',
            'observer' => 'observers',
            'admin' => 'admins',
            default => 'users'
        };

        DB::table($table)
            ->where('id', $this->passwordUserId)
            ->update([
                'password' => bcrypt($this->newPassword),
                'updated_at' => now()
            ]);

        session()->flash('success', 'Password updated successfully.');
        $this->closePasswordModal();
    }
    
    private function sendStatusNotification($userId, $type, $status)
    {
        $user = match($type) {
            'candidate' => Candidate::with('user')->find($userId)?->user,
            'voter' => User::find($userId),
            'observer' => Observer::find($userId),
            'admin' => Admin::find($userId),
            default => User::find($userId)
        };
        
        if (!$user) return;
        
        // Check if notifications are enabled by admin
        $notificationSettings = DB::table('notification_settings')->first();
        if (!$notificationSettings) return;
        
        $message = $status === 'suspended' ? 'Your account has been suspended.' : 'Your account has been reactivated.';
        
        // Send email if enabled and template exists
        if ($notificationSettings->email_enabled && $notificationSettings->suspension_email_template) {
            // Email notification logic would go here
            Log::info('Email notification sent', ['user_id' => $userId, 'type' => $type, 'status' => $status]);
        }

        // Send SMS if enabled and template exists
        if ($notificationSettings->sms_enabled && $notificationSettings->suspension_sms_template && $user->phone_number) {
            // SMS notification logic would go here
            Log::info('SMS notification sent', ['user_id' => $userId, 'type' => $type, 'status' => $status]);
        }
        
        // Send in-app notification if enabled (compulsory if set by admin)
        if ($notificationSettings->inapp_enabled) {
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'user_type' => $type,
                'title' => 'Account Status Update',
                'message' => $message,
                'type' => 'status_change',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    public function confirmStatusChange()
    {
        if (!$this->confirmAction || !$this->confirmUserId || !$this->confirmUserType) {
            return;
        }
        
        // Validate suspension reason
        if ($this->confirmAction === 'suspend') {
            if (empty(trim($this->suspensionReason))) {
                $this->addError('suspensionReason', 'Suspension reason is required.');
                return;
            }
        }
        
        $status = match($this->confirmAction) {
            'approve' => 'approved',
            'reject' => 'rejected',
            'suspend' => 'suspended',
            'unsuspend' => 'approved',
            'review' => 'review',
            'hold' => 'temporary_hold',
            'expire' => 'expired',
            'require_renewal' => 'renewal_required'
        };
        
        $this->updateStatus($this->confirmUserId, $this->confirmUserType, $status);
        
        // Send notifications for suspension/unsuspension (only in production)
        if (in_array($this->confirmAction, ['suspend', 'unsuspend']) && !app()->environment('local')) {
            $this->sendStatusNotification($this->confirmUserId, $this->confirmUserType, $status);
        }
        
        $this->closeConfirmModal();
    }
    
    public function saveUser()
    {
        $this->validate([
            'newUser.first_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'newUser.last_name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'newUser.email' => 'required|email|unique:users,email|unique:candidates,email|unique:observers,email|unique:admins,email',
            'newUser.phone_number' => 'required|string|regex:/^\+?[1-9]\d{1,14}$/',
            'newUser.type' => 'required|in:voter,candidate,observer,admin',
            'newUser.password' => 'required|string|min:8'
        ], [
            'newUser.first_name.regex' => 'First name can only contain letters and spaces.',
            'newUser.last_name.regex' => 'Last name can only contain letters and spaces.',
            'newUser.phone_number.regex' => 'Please enter a valid phone number.',
        ]);
        
        $table = match($this->newUser['type']) {
            'voter' => 'users',
            'candidate' => 'candidates',
            'observer' => 'observers', 
            'admin' => 'admins'
        };
        
        $userData = [
            'first_name' => $this->newUser['first_name'],
            'last_name' => $this->newUser['last_name'],
            'email' => $this->newUser['email'],
            'phone_number' => $this->newUser['phone_number'],
            'password' => bcrypt($this->newUser['password']),
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        // Add additional fields for users table (matching registration)
        if ($this->newUser['type'] === 'voter') {
            $userData['uuid'] = Str::uuid();
            $userData['id_number_hash'] = 'admin_created';
            $userData['id_salt'] = Str::random(32);
            $userData['role'] = 'voter';
        }
        
        DB::table($table)->insert($userData);
        
        session()->flash('success', ucfirst($this->newUser['type']) . ' created successfully.');
        $this->closeAddUserModal();
    }
    


    public function updateStatus($userId, $type, $status)
    {
        if (!Gate::allows('manage_users')) {
            abort(403, 'This action is unauthorized.');
        }

        $admin = auth('admin')->user();

        Log::info('updateStatus called', ['userId' => $userId, 'type' => $type, 'status' => $status]);

        $table = match($type) {
            'voter' => 'users',
            'candidate' => 'candidates',
            'observer' => 'observers',
            'admin' => 'admins',
            default => 'users'
        };

        Log::info('Using table', ['table' => $table]);

        $updateData = [
            'status' => $status,
            'approved_at' => $status === 'approved' ? now() : null,
            'approved_by' => auth('admin')->id()
        ];

        // Add suspension-specific data
        if ($status === 'suspended') {
            $updateData['suspended_at'] = now();
            $updateData['suspended_by'] = auth('admin')->id();
            $updateData['suspension_reason'] = $this->suspensionReason;
        }

        $result = DB::table($table)
            ->where('id', $userId)
            ->update($updateData);

        Log::info('Update result', ['affected_rows' => $result]);

        session()->flash('success', ucfirst($type) . ' status updated successfully.');
    }

    public function render()
    {
        $query = collect();

        // Get voters
        if ($this->typeFilter === 'all' || $this->typeFilter === 'voter') {
            $voters = DB::table('users')
                ->select('id', 'email', 'first_name', 'last_name', 'status', 'created_at', 'phone_number', DB::raw("'voter' as type"), DB::raw("'voter' as role"))
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('email', 'like', "%{$this->search}%")
                              ->orWhere('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('phone_number', 'like', "%{$this->search}%")
                              ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$this->search}%");
                    });
                })
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get();
            $query = $query->merge($voters);
        }

        // Get candidates
        if ($this->typeFilter === 'all' || $this->typeFilter === 'candidate') {
            $candidates = Candidate::with('user')
                ->when($this->search, function($q) {
                    $q->whereHas('user', function($query) {
                        $query->where('email', 'like', "%{$this->search}%")
                              ->orWhere('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('phone_number', 'like', "%{$this->search}%")
                              ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$this->search}%");
                    });
                })
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get()
                ->map(function($candidate) {
                    return (object) [
                        'id' => $candidate->id,
                        'email' => $candidate->user->email,
                        'first_name' => $candidate->user->first_name,
                        'last_name' => $candidate->user->last_name,
                        'status' => $candidate->status->value,
                        'created_at' => $candidate->created_at,
                        'phone_number' => $candidate->user->phone_number,
                        'type' => 'candidate',
                        'role' => 'candidate'
                    ];
                });
            $query = $query->merge($candidates);
        }

        // Get observers
        if ($this->typeFilter === 'all' || $this->typeFilter === 'observer') {
            $observers = DB::table('observers')
                ->select('id', 'email', 'first_name', 'last_name', 'status', 'created_at', 'phone_number', DB::raw("'observer' as type"), DB::raw("'observer' as role"))
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('email', 'like', "%{$this->search}%")
                              ->orWhere('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('phone_number', 'like', "%{$this->search}%")
                              ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$this->search}%");
                    });
                })
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get();
            $query = $query->merge($observers);
        }

        // Get admins
        if ($this->typeFilter === 'all' || $this->typeFilter === 'admin') {
            $admins = DB::table('admins')
                ->select('id', 'email', 'first_name', 'last_name', 'status', 'created_at', 'phone_number', DB::raw("'admin' as type"), DB::raw("'admin' as role"))
                ->when($this->search, function($q) {
                    $q->where(function($query) {
                        $query->where('email', 'like', "%{$this->search}%")
                              ->orWhere('first_name', 'like', "%{$this->search}%")
                              ->orWhere('last_name', 'like', "%{$this->search}%")
                              ->orWhere('phone_number', 'like', "%{$this->search}%")
                              ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', "%{$this->search}%");
                    });
                })
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get();
            $query = $query->merge($admins);
        }

        // Sort users
        $allUsers = $query->sortByDesc('created_at');
        
        // Calculate stats
        $totalStats = [
            'total' => DB::table('users')->count() + 
                      DB::table('candidates')->count() + 
                      DB::table('observers')->count() + 
                      DB::table('admins')->count(),
            'approved' => DB::table('users')->where('status', 'approved')->count() + 
                         DB::table('candidates')->where('status', 'approved')->count() + 
                         DB::table('observers')->where('status', 'approved')->count() + 
                         DB::table('admins')->where('status', 'approved')->count(),
            'pending' => DB::table('users')->where('status', 'pending')->count() + 
                        DB::table('candidates')->where('status', 'pending')->count() + 
                        DB::table('observers')->where('status', 'pending')->count() + 
                        DB::table('admins')->where('status', 'pending')->count(),
            'suspended' => DB::table('users')->where('status', 'suspended')->count() + 
                          DB::table('candidates')->where('status', 'suspended')->count() + 
                          DB::table('observers')->where('status', 'suspended')->count() + 
                          DB::table('admins')->where('status', 'suspended')->count(),
            'rejected' => DB::table('users')->where('status', 'rejected')->count() +
                          DB::table('candidates')->where('status', 'rejected')->count() +
                          DB::table('observers')->where('status', 'rejected')->count() +
                          DB::table('admins')->where('status', 'rejected')->count(),
            'temporary_hold' => DB::table('users')->where('status', 'temporary_hold')->count(),
            'expired' => DB::table('users')->where('status', 'expired')->count(),
            'renewal_required' => DB::table('users')->where('status', 'renewal_required')->count(),
        ];

        return view('livewire.admin.users.index', [
            'users' => $allUsers,
            'totalStats' => $totalStats
        ]);
    }
}