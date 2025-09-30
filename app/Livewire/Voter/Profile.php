<?php

namespace App\Livewire\Voter;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ImageUploadService;
use App\Models\User;

class Profile extends Component
{
    use WithFileUploads;
    
    public $first_name;
    public $last_name;
    public $email;
    public $phone_number;
    public $city;
    public $occupation;
    public $about_me;
    public $bio;
    public $skills;
    public $highest_qualification;
    public $field_of_study;
    public $employment_status;
    public $date_of_birth;
    public $linkedin_handle;
    public $twitter_handle;
    public $is_public;
    public $email_public;
    public $phone_public;
    public $profile_image;
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    protected $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone_number' => 'required|string|max:20',
        'city' => 'required|string|max:255',
        'occupation' => 'required|string|max:255',
        'about_me' => 'required|string|max:300',
        'bio' => 'nullable|string|max:1000',
        'skills' => 'nullable|string|max:500',
        'highest_qualification' => 'nullable|string|max:255',
        'field_of_study' => 'nullable|string|max:255',
        'employment_status' => 'nullable|string|max:255',
        'date_of_birth' => 'nullable|date|before:today',
        'linkedin_handle' => 'nullable|string|max:255',
        'twitter_handle' => 'nullable|string|max:255',
        'is_public' => 'boolean',
        'email_public' => 'boolean',
        'phone_public' => 'boolean',
        'profile_image' => 'nullable|image|max:5120',
        'current_password' => 'nullable|string',
        'new_password' => 'nullable|string|min:8|confirmed',
    ];

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('voter.login');
        }

        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->city = $user->city;
        $this->occupation = $user->occupation;
        $this->about_me = $user->about_me;
        $this->bio = $user->bio;
        $this->skills = $user->skills;
        $this->highest_qualification = $user->highest_qualification;
        $this->field_of_study = $user->field_of_study;
        $this->employment_status = $user->employment_status;
        $this->date_of_birth = $user->date_of_birth?->format('Y-m-d');
        $this->linkedin_handle = $user->linkedin_handle;
        $this->twitter_handle = $user->twitter_handle;
        $this->is_public = $user->is_public ?? true;
        $this->email_public = $user->email_public ?? true;
        $this->phone_public = $user->phone_public ?? true;
    }

    /**
     * Verify profile image content type by reading actual file content
     */
    private function verifyProfileImageContentType(): void
    {
        if (!$this->profile_image) {
            return;
        }

        // Get actual MIME type from file content
        $actualMimeType = $this->getActualMimeType($this->profile_image->getRealPath());

        // Define allowed MIME types for profile images
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        if (!in_array($actualMimeType, $allowedMimeTypes)) {
            \Illuminate\Support\Facades\Log::warning('Profile image upload blocked: invalid content type', [
                'user_id' => Auth::id(),
                'client_mime' => $this->profile_image->getMimeType(),
                'actual_mime' => $actualMimeType,
                'file_name' => $this->profile_image->getClientOriginalName(),
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'profile_image' => ['The uploaded file content does not match the expected image format. Only JPEG, PNG, GIF, and WebP images are allowed.']
            ]);
        }

        // Additional security check: verify file extension matches content
        $extension = strtolower($this->profile_image->getClientOriginalExtension());
        $expectedExtensions = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp']
        ];

        if (isset($expectedExtensions[$actualMimeType]) && !in_array($extension, $expectedExtensions[$actualMimeType])) {
            \Illuminate\Support\Facades\Log::warning('Profile image upload blocked: extension mismatch', [
                'user_id' => Auth::id(),
                'actual_mime' => $actualMimeType,
                'extension' => $extension,
                'expected_extensions' => $expectedExtensions[$actualMimeType],
            ]);

            throw \Illuminate\Validation\ValidationException::withMessages([
                'profile_image' => ['File extension does not match the image content type.']
            ]);
        }
    }

    /**
     * Get actual MIME type from file content
     */
    private function getActualMimeType(string $filePath): string
    {
        // Use finfo for accurate MIME type detection
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        }

        // Fallback to mime_content_type if finfo is not available
        return mime_content_type($filePath);
    }

    public function updatedProfileImage()
    {
        $this->validate([
            'profile_image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120'
        ], [
            'profile_image.required' => 'Please select an image file.',
            'profile_image.image' => 'The file must be an image.',
            'profile_image.mimes' => 'Only JPEG, PNG, GIF, and WebP images are allowed.',
            'profile_image.max' => 'Image size must be less than 2MB.'
        ]);

        // Server-side content-type verification
        $this->verifyProfileImageContentType();

        $user = Auth::user();
        $imagePath = app(ImageUploadService::class)->uploadProfileImage($this->profile_image, $user);

        $user->update(['profile_image' => $imagePath]);
        $this->reset('profile_image');

        Auth::setUser($user->fresh());
        session()->flash('profile_updated', 'Profile image updated successfully!');
        $this->dispatch('toast', type: 'success', message: 'Profile image updated successfully!');
    }

    public function updateProfile()
    {
        $this->validate();
        
        $user = Auth::user();
        
        // Build update data - exclude names for verified users
        $updateData = [
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'city' => $this->city,
            'occupation' => $this->occupation,
            'about_me' => $this->about_me,
            'bio' => $this->bio,
            'skills' => $this->skills,
            'highest_qualification' => $this->highest_qualification,
            'field_of_study' => $this->field_of_study,
            'employment_status' => $this->employment_status,
            'date_of_birth' => $this->date_of_birth,
            'linkedin_handle' => $this->linkedin_handle,
            'twitter_handle' => $this->twitter_handle,
            'is_public' => $this->is_public,
            'email_public' => $this->email_public,
            'phone_public' => $this->phone_public,
        ];
        
        // Only allow name changes for unverified users
        if (!in_array($user->status->value, ['approved', 'accredited'])) {
            $updateData['first_name'] = $this->first_name;
            $updateData['last_name'] = $this->last_name;
        }
        
        $user->update($updateData);
        session()->flash('profile_updated', 'Profile updated successfully!');
        $this->dispatch('toast', type: 'success', message: 'Profile updated successfully!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('password_updated', 'Password updated successfully!');
        $this->dispatch('toast', type: 'success', message: 'Password updated successfully!');
        $this->dispatch('toast', type: 'success', message: 'Password updated successfully!');
    }

    public function render()
    {
        return view('livewire.voter.profile');
    }


}