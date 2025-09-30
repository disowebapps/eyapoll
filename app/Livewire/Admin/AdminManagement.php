<?php

namespace App\Livewire\Admin;

use Livewire\WithPagination;
use App\Models\Admin;
use App\Models\Observer;
use Illuminate\Support\Facades\Hash;

class AdminManagement extends BaseAdminComponent
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all';
    public $statusFilter = 'all';
    public $showCreateModal = false;
    public $editingAdmin = null;
    public $showAdminModal = false;
    public $selectedAdmin = null;

    // Form fields
    public $email = '';
    public $first_name = '';
    public $last_name = '';
    public $phone_number = '';
    public $password = '';
    public $password_confirmation = '';
    public $permissions = [];
    public $user_type = 'admin';

    protected $rules = [
        'email' => 'required|email|unique:admins,email',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:20',
        'password' => 'required|string|min:8|confirmed',
        'user_type' => 'required|in:admin,observer',
        'permissions' => 'array',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Admin::class);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->hasPermission('manage_users')) {
            abort(403, 'This action is unauthorized.');
        }
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->editingAdmin = null;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->email = '';
        $this->first_name = '';
        $this->last_name = '';
        $this->phone_number = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->permissions = [];
        $this->user_type = 'admin';
    }

    public function save()
    {
        if ($this->editingAdmin) {
            $this->rules['email'] = 'required|email|unique:admins,email,' . $this->editingAdmin;
            $this->rules['password'] = 'nullable|string|min:8|confirmed';
        }

        $this->validate();

        $data = [
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'permissions' => $this->permissions,
            'status' => 'approved',
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user_type === 'admin') {
            if ($this->editingAdmin) {
                Admin::find($this->editingAdmin)->update($data);
                $message = 'Admin updated successfully.';
            } else {
                Admin::create($data);
                $message = 'Admin created successfully.';
            }
        } else {
            if ($this->editingAdmin) {
                Observer::find($this->editingAdmin)->update($data);
                $message = 'Observer updated successfully.';
            } else {
                Observer::create($data);
                $message = 'Observer created successfully.';
            }
        }

        session()->flash('success', $message);
        $this->closeModal();
    }

    public function edit($id, $type)
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->hasPermission('manage_users')) {
            abort(403, 'This action is unauthorized.');
        }
        
        $user = $type === 'admin' ? Admin::find($id) : Observer::find($id);
        
        $this->editingAdmin = $id;
        $this->user_type = $type;
        $this->email = $user->email;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->phone_number = $user->phone_number;
        $this->permissions = $user->permissions ?? [];
        $this->closeAdminModal();
        $this->showCreateModal = true;
    }

    public function updateStatus($id, $type, $status)
    {
        $admin = auth('admin')->user();
        if (!$admin || !$admin->hasPermission('manage_users')) {
            abort(403, 'This action is unauthorized.');
        }
        
        $model = $type === 'admin' ? Admin::class : Observer::class;
        $model::find($id)->update(['status' => $status]);
        
        session()->flash('success', ucfirst($type) . ' status updated successfully.');
    }

    public function viewAdmin($id)
    {
        // Find the user in the current filtered results to get the correct type
        $users = $this->render()->getData()['users'];
        $userData = $users->firstWhere('id', $id);
        
        if ($userData) {
            $user = $userData->type === 'admin' ? Admin::find($id) : Observer::find($id);
            if ($user) {
                $this->selectedAdmin = $user;
                $this->showAdminModal = true;
            }
        }
    }

    public function closeAdminModal()
    {
        $this->showAdminModal = false;
        $this->selectedAdmin = null;
    }

    public function render()
    {
        $admins = collect();
        
        if ($this->typeFilter === 'all' || $this->typeFilter === 'admin') {
            $adminUsers = Admin::query()
                ->when($this->search, fn($q) => $q->where('email', 'like', "%{$this->search}%")
                    ->orWhere('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%"))
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get()
                ->map(fn($admin) => (object) array_merge($admin->toArray(), ['type' => 'admin']));
            
            $admins = $admins->merge($adminUsers);
        }

        if ($this->typeFilter === 'all' || $this->typeFilter === 'observer') {
            $observers = Observer::query()
                ->when($this->search, fn($q) => $q->where('email', 'like', "%{$this->search}%")
                    ->orWhere('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%"))
                ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
                ->get()
                ->map(fn($observer) => (object) array_merge($observer->toArray(), ['type' => 'observer']));
            
            $admins = $admins->merge($observers);
        }

        return view('livewire.admin.admin-management', [
            'users' => $admins->sortByDesc('created_at')->take(50),
            'availablePermissions' => [
                'manage_elections' => 'Manage Elections',
                'manage_users' => 'Manage Users',
                'manage_candidates' => 'Manage Candidates',
                'view_reports' => 'View Reports',
                'system_settings' => 'System Settings',
            ]
        ]);
    }
}