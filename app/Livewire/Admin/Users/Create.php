<?php

namespace App\Livewire\Admin\Users;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Create extends Component
{
    public $userData = [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'phone_number' => '',
        'role' => 'voter',
        'date_of_birth' => '',
        'highest_qualification' => '',
        'location_type' => '',
        'abroad_city' => '',
        'current_occupation' => '',
        'marital_status' => '',
        'field_of_study' => '',
        'student_status' => '',
        'employment_status' => '',
        'skills' => '',
    ];

    public function save()
    {
        $emailValidation = 'required|email|unique:users,email';

        $this->validate([
            'userData.first_name' => 'required|string|max:255',
            'userData.last_name' => 'required|string|max:255',
            'userData.email' => $emailValidation,
            'userData.phone_number' => 'required|string|max:20',
            'userData.role' => 'required|in:voter',
        ]);

        $baseData = [
            'first_name' => $this->userData['first_name'],
            'last_name' => $this->userData['last_name'],
            'email' => $this->userData['email'],
            'phone_number' => $this->userData['phone_number'] ?: null,
            'password' => bcrypt('12345678'),
            'status' => 'pending',
            'date_of_birth' => $this->userData['date_of_birth'] ?: null,
            'highest_qualification' => $this->userData['highest_qualification'] ?: null,
            'location_type' => $this->userData['location_type'] ?: null,
            'abroad_city' => $this->userData['abroad_city'] ?: null,
            'current_occupation' => $this->userData['current_occupation'] ?: null,
            'marital_status' => $this->userData['marital_status'] ?: null,
            'field_of_study' => $this->userData['field_of_study'] ?: null,
            'student_status' => $this->userData['student_status'] ?: null,
            'employment_status' => $this->userData['employment_status'] ?: null,
            'skills' => $this->userData['skills'] ?: null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $baseData['uuid'] = Str::uuid();
        $baseData['id_number_hash'] = 'admin_created';
        $baseData['id_salt'] = Str::random(32);
        $baseData['role'] = 'voter';
        DB::table('users')->insert($baseData);

        session()->flash('success', ucfirst($this->userData['role']) . ' created successfully.');
        return redirect()->route('admin.users.index');
    }

    public function getRoleFields()
    {
        return ['basic', 'kyc', 'education', 'location', 'skills'];
    }

    public function render()
    {
        return view('livewire.admin.users.create');
    }
}