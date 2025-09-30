<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use App\Models\User;
use App\Models\Admin;
use App\Models\Candidate\Candidate;
use App\Models\Observer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Admin\UserManagementService;

class Show extends Component
{
    public $userId;
    public $userType;
    public $user;
    public $editing = false;
    public $editData = [];

    public function mount($userId, $userType, $user)
    {
        $this->userId = $userId;
        $this->user = $user;
        $this->userType = $userType;
        
        if (!$this->user) {
            abort(404, 'User not found');
        }
        
        $this->resetEditData();
    }

    public function resetEditData()
    {
        // Get the actual user data regardless of type
        $userData = $this->getUserData();
        
        $this->editData = [
            'first_name' => $userData['first_name'] ?? '',
            'last_name' => $userData['last_name'] ?? '',
            'email' => $userData['email'] ?? '',
            'phone_number' => $userData['phone_number'] ?? '',
            'status' => $userData['status'] ?? 'pending',
            'role' => $this->userType,
            'date_of_birth' => $userData['date_of_birth'] ?? null,
            'highest_qualification' => $userData['highest_qualification'] ?? null,
            'location_type' => $userData['location_type'] ?? null,
            'abroad_city' => $userData['abroad_city'] ?? null,
            'current_occupation' => $userData['current_occupation'] ?? null,
            'marital_status' => $userData['marital_status'] ?? null,
            'field_of_study' => $userData['field_of_study'] ?? null,
            'student_status' => $userData['student_status'] ?? null,
            'employment_status' => $userData['employment_status'] ?? null,
            'skills' => $userData['skills'] ?? null,
            'is_executive' => $userData['is_executive'] ?? false,
            'current_position' => $userData['current_position'] ?? null,
            'executive_order' => $userData['executive_order'] ?? null,
            'term_start' => $userData['term_start'] ?? null,
            'term_end' => $userData['term_end'] ?? null,
        ];
    }
    
    private function getUserData(): array
    {
        // Handle actual Candidate model (from candidates table)
        if ($this->userType === 'candidate' && isset($this->user->user)) {
            $user = $this->user->user;
            return [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => is_string($this->user->status) ? $this->user->status : $this->user->status->value,
                'date_of_birth' => $user->date_of_birth,
                'highest_qualification' => $user->highest_qualification,
                'location_type' => $user->location_type,
                'abroad_city' => $user->abroad_city,
                'current_occupation' => $user->current_occupation,
                'marital_status' => $user->marital_status,
                'field_of_study' => $user->field_of_study,
                'student_status' => $user->student_status,
                'employment_status' => $user->employment_status,
                'skills' => $user->skills,
                'is_executive' => $user->is_executive ?? false,
                'current_position' => $user->current_position ?? null,
                'executive_order' => $user->executive_order ?? null,
                'term_start' => $user->term_start ?? null,
                'term_end' => $user->term_end ?? null,
            ];
        }
        
        // Handle User model (from users table) - including users with role="candidate"
        return [
            'first_name' => $this->user->first_name ?? '',
            'last_name' => $this->user->last_name ?? '',
            'email' => $this->user->email ?? '',
            'phone_number' => $this->user->phone_number ?? '',
            'status' => is_string($this->user->status) ? $this->user->status : ($this->user->status->value ?? 'pending'),
            'date_of_birth' => $this->user->date_of_birth ?? null,
            'highest_qualification' => $this->user->highest_qualification ?? null,
            'location_type' => $this->user->location_type ?? null,
            'abroad_city' => $this->user->abroad_city ?? null,
            'current_occupation' => $this->user->current_occupation ?? null,
            'marital_status' => $this->user->marital_status ?? null,
            'field_of_study' => $this->user->field_of_study ?? null,
            'student_status' => $this->user->student_status ?? null,
            'employment_status' => $this->user->employment_status ?? null,
            'skills' => $this->user->skills ?? null,
            'is_executive' => $this->user->is_executive ?? false,
            'current_position' => $this->user->current_position ?? null,
            'executive_order' => $this->user->executive_order ?? null,
            'term_start' => $this->user->term_start ?? null,
            'term_end' => $this->user->term_end ?? null,
        ];
    }

    public function startEdit()
    {
        $this->editing = true;
        $this->resetEditData();
    }

    public function cancelEdit()
    {
        $this->editing = false;
        $this->resetEditData();
    }

    public function saveBasicInfo()
    {
        $this->validate([
            'editData.first_name' => 'required|string|max:255',
            'editData.last_name' => 'required|string|max:255',
            'editData.email' => 'required|email|max:255',
            'editData.phone_number' => 'required|string|max:20',
            'editData.date_of_birth' => 'nullable|date',
            'editData.marital_status' => 'nullable|in:single,married,divorced,widowed',
        ]);

        $this->updateUserSection([
            'first_name' => $this->editData['first_name'],
            'last_name' => $this->editData['last_name'],
            'email' => $this->editData['email'],
            'phone_number' => $this->editData['phone_number'],
            'date_of_birth' => $this->editData['date_of_birth'],
            'marital_status' => $this->editData['marital_status'],
        ], 'Basic information updated successfully.');
    }

    public function saveAccountSettings()
    {
        $this->validate([
            'editData.status' => 'required|in:pending,approved,accredited,rejected,suspended',
            'editData.role' => 'required|in:voter,candidate,observer,admin',
        ]);

        if ($this->editData['role'] !== $this->userType) {
            $this->changeUserRole();
            return;
        }

        $this->updateUserSection([
            'status' => $this->editData['status'],
        ], 'Account settings updated successfully.');
    }

    public function saveEducationCareer()
    {
        $this->validate([
            'editData.highest_qualification' => 'nullable|string|max:255',
            'editData.field_of_study' => 'nullable|string|max:255',
            'editData.student_status' => 'nullable|in:current_student,graduate,dropout',
            'editData.employment_status' => 'nullable|in:employed,unemployed,self_employed,student',
            'editData.current_occupation' => 'nullable|string|max:255',
            'editData.skills' => 'nullable|string',
        ]);

        $this->updateUserSection([
            'highest_qualification' => $this->editData['highest_qualification'],
            'field_of_study' => $this->editData['field_of_study'],
            'student_status' => $this->editData['student_status'],
            'employment_status' => $this->editData['employment_status'],
            'current_occupation' => $this->editData['current_occupation'],
            'skills' => $this->editData['skills'],
        ], 'Education & career updated successfully.');
    }

    public function saveLocationWork()
    {
        $this->validate([
            'editData.location_type' => 'nullable|in:home,abroad',
            'editData.abroad_city' => 'nullable|string|max:255',
        ]);

        $this->updateUserSection([
            'location_type' => $this->editData['location_type'],
            'abroad_city' => $this->editData['abroad_city'],
        ], 'Location updated successfully.');
    }

    public function saveExecutiveInfo()
    {
        $this->validate([
            'editData.is_executive' => 'boolean',
            'editData.current_position' => 'nullable|string|max:255',
            'editData.executive_order' => 'nullable|integer|min:1|max:100',
            'editData.term_start' => 'nullable|date',
            'editData.term_end' => 'nullable|date|after:editData.term_start',
        ]);

        $this->updateUserSection([
            'is_executive' => $this->editData['is_executive'],
            'current_position' => $this->editData['current_position'],
            'executive_order' => $this->editData['executive_order'],
            'term_start' => $this->editData['term_start'],
            'term_end' => $this->editData['term_end'],
        ], 'Executive information updated successfully.');
    }

    private function updateUserSection(array $data, string $successMessage)
    {
        try {
            DB::beginTransaction();
            
            $userService = app(UserManagementService::class);
            $userService->updateUser($this->userId, $this->userType, $data);
            
            DB::commit();
            session()->flash('success', $successMessage);
            $this->refreshUserData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User section update failed', [
                'user_id' => $this->userId,
                'user_type' => $this->userType,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update: ' . $e->getMessage());
        }
    }
    


    private function changeUserRole()
    {
        $userService = app(UserManagementService::class);
        $userService->changeUserRole($this->userId, $this->userType, $this->editData['role']);
        
        $this->userType = $this->editData['role'];
        session()->flash('success', 'User role updated successfully.');
        $this->editing = false;
    }


    public function accreditUser()
    {
        // Security checks
        if (!auth('admin')->check()) {
            abort(403, 'Unauthorized');
        }
        
        if ($this->user->status !== \App\Enums\Auth\UserStatus::APPROVED) {
            session()->flash('error', 'Only approved users can be accredited.');
            return;
        }
        
        if (!$this->user->hasVerifiedDocuments()) {
            session()->flash('error', 'User must have verified KYC documents.');
            return;
        }
        
        try {
            $accreditationService = app(\App\Services\AccreditationService::class);
            $result = $accreditationService->accreditUser($this->user, auth('admin')->id());
            
            if ($result['success']) {
                session()->flash('success', "User accredited successfully. Generated {$result['tokens_created']} vote tokens.");
            } else {
                session()->flash('error', 'Accreditation failed.');
            }
            
            return redirect()->route('admin.users.show', $this->userId);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Accreditation failed', [
                'user_id' => $this->user->id,
                'admin_id' => auth('admin')->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'System error during accreditation.');
        }
    }

    public function suspendUser()
    {
        try {
            DB::beginTransaction();
            
            $table = match($this->userType) {
                'candidate' => 'candidates',
                'voter' => 'users',
                'admin' => 'admins',
                'observer' => 'observers',
                default => 'users'
            };
            
            DB::table($table)
                ->where('id', $this->userId)
                ->update([
                    'status' => 'suspended',
                    'suspended_at' => now(),
                    'suspended_by' => auth('admin')->id(),
                    'updated_at' => now()
                ]);
            
            DB::commit();
            session()->flash('success', 'User suspended successfully.');
            $this->refreshUserData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User suspension failed', [
                'user_id' => $this->userId,
                'user_type' => $this->userType,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to suspend user: ' . $e->getMessage());
        }
    }

    public function unsuspendUser()
    {
        try {
            DB::beginTransaction();
            
            $table = match($this->userType) {
                'candidate' => 'candidates',
                'voter' => 'users',
                'admin' => 'admins',
                'observer' => 'observers',
                default => 'users'
            };
            
            DB::table($table)
                ->where('id', $this->userId)
                ->update([
                    'status' => 'approved',
                    'suspended_at' => null,
                    'suspended_by' => null,
                    'suspension_reason' => null,
                    'updated_at' => now()
                ]);
            
            DB::commit();
            session()->flash('success', 'User unsuspended successfully.');
            $this->refreshUserData();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User unsuspension failed', [
                'user_id' => $this->userId,
                'user_type' => $this->userType,
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to unsuspend user: ' . $e->getMessage());
        }
    }

    private function refreshUserData()
    {
        try {
            // Reload user data from database
            switch ($this->userType) {
                case 'candidate':
                    $this->user = Candidate::with('user')->find($this->userId);
                    break;
                case 'voter':
                    $this->user = User::find($this->userId);
                    break;
                case 'observer':
                    $this->user = Observer::find($this->userId);
                    break;
                case 'admin':
                    $this->user = Admin::find($this->userId);
                    break;
                default:
                    $this->user = User::find($this->userId);
            }
            
            if (!$this->user) {
                throw new \Exception('User not found after refresh');
            }
            
            $this->resetEditData();
            
        } catch (\Exception $e) {
            Log::error('User data refresh failed', [
                'user_id' => $this->userId,
                'user_type' => $this->userType,
                'error' => $e->getMessage()
            ]);
            abort(404, 'User not found');
        }
    }

    public function render()
    {
        return view('livewire.admin.users.show');
    }
}