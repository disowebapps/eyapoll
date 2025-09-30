<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Admin;
use App\Models\Candidate\Candidate;
use App\Models\Observer;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function show($userId)
    {
        // Always try users table first - this is the primary table
        $user = User::find($userId);
        
        if ($user) {
            $userType = $user->role->value ?? 'voter';
            return view('admin.users.show', compact('user', 'userType', 'userId'));
        }
        
        // If not in users table, check if this is a candidate ID
        $candidate = Candidate::with('user')->find($userId);
        if ($candidate && $candidate->user) {
            return redirect()->route('admin.users.show', $candidate->user->id);
        }
        
        // Check admin table
        $admin = Admin::find($userId);
        if ($admin) {
            $user = $admin;
            $userType = 'admin';
            return view('admin.users.show', compact('user', 'userType', 'userId'));
        }
        
        // Check observer table
        $observer = Observer::find($userId);
        if ($observer) {
            $user = $observer;
            $userType = 'observer';
            return view('admin.users.show', compact('user', 'userType', 'userId'));
        }
        
        abort(404, 'User not found');
    }
}