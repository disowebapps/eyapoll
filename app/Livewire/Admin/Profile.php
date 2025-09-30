<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ImageUploadService;

class Profile extends Component
{
    use WithFileUploads;
    
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $profile_image;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }
        
        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->email = $user->email ?? '';
        $this->phone_number = $user->phone_number ?? '';
    }

    public function updatedProfileImage()
    {
        session()->flash('profile_updated', 'File detected: ' . $this->profile_image->getClientOriginalName());
        
        $this->validate([
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120'
        ], [
            'profile_image.required' => 'Please select an image file.',
            'profile_image.image' => 'The file must be an image.',
            'profile_image.mimes' => 'Only JPEG, PNG, GIF, and WebP images are allowed.',
            'profile_image.max' => 'Image size must be less than 2MB.'
        ]);
        
        $user = Auth::guard('admin')->user();
        $imagePath = app(ImageUploadService::class)->uploadProfileImage($this->profile_image, $user);
        
        $user->update(['profile_image' => $imagePath]);
        $this->reset('profile_image');
        
        Auth::guard('admin')->setUser($user->fresh());
        session()->flash('profile_updated', 'Profile image updated successfully!');
    }

    public function updateProfile()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . Auth::guard('admin')->id(),
            'phone_number' => 'required|string|max:20',
        ]);

        Auth::guard('admin')->user()->update([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
        ]);

        session()->flash('profile_updated', 'Profile updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::guard('admin')->user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        Auth::guard('admin')->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_updated', 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.admin.profile');
    }
}