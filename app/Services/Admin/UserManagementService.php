<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Admin;
use App\Models\Observer;
use App\Models\Candidate\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserManagementService
{
    public function updateUser($userId, $userType, array $data)
    {
        DB::beginTransaction();
        
        try {
            switch ($userType) {
                case 'candidate':
                    // Check if this is an actual Candidate model or a User with role="candidate"
                    $candidate = Candidate::with('user')->find($userId);
                    if ($candidate && $candidate->user) {
                        // This is an actual candidate record
                        $candidate->user->update([
                            'first_name' => $data['first_name'],
                            'last_name' => $data['last_name'],
                            'email' => $data['email'],
                            'phone_number' => $data['phone_number'],
                            'date_of_birth' => $data['date_of_birth'],
                            'highest_qualification' => $data['highest_qualification'],
                            'location_type' => $data['location_type'],
                            'abroad_city' => $data['abroad_city'],
                            'current_occupation' => $data['current_occupation'],
                            'marital_status' => $data['marital_status'],
                            'field_of_study' => $data['field_of_study'],
                            'student_status' => $data['student_status'],
                            'employment_status' => $data['employment_status'],
                            'skills' => $data['skills'],
                        ]);
                        $candidate->update(['status' => $data['status']]);
                    } else {
                        // This is a User with role="candidate"
                        User::where('id', $userId)->update($data);
                    }
                    break;
                    
                case 'voter':
                    User::where('id', $userId)->update($data);
                    break;
                    
                case 'admin':
                    Admin::where('id', $userId)->update($data);
                    break;
                    
                case 'observer':
                    Observer::where('id', $userId)->update($data);
                    break;
                    
                default:
                    throw new \Exception("Unknown user type: {$userType}");
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function changeUserRole($userId, $currentType, $newRole)
    {
        DB::beginTransaction();
        
        try {
            $actualUserId = match($currentType) {
                'candidate' => Candidate::with('user')->find($userId)?->user?->id,
                'voter' => $userId,
                'admin' => User::where('email', Admin::find($userId)?->email)->first()?->id,
                'observer' => User::where('email', Observer::find($userId)?->email)->first()?->id,
                default => $userId
            };
            
            if (!$actualUserId) {
                throw new \Exception('Could not find user record');
            }

            User::where('id', $actualUserId)->update(['role' => $newRole]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}