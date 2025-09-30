<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center space-x-6">
            <div class="relative cursor-pointer" x-data="{}" @click="$refs.profileImageInput.click()">
                <img src="{{ Auth::user()->profile_image_url }}" 
                     alt="Profile" 
                     class="w-20 h-20 rounded-2xl object-cover shadow-lg hover:opacity-80 transition-opacity">
                <div class="absolute -bottom-2 -right-2 w-6 h-6 bg-green-400 border-4 border-white rounded-full"></div>
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-2xl opacity-0 hover:opacity-100 transition-opacity">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <input type="file" x-ref="profileImageInput" wire:model="profile_image" accept="image/*" class="hidden">
            </div>
            @error('profile_image') 
                <div class="mt-2 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                    {{ $message }}
                </div> 
            @enderror
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900">{{ Auth::user()->full_name }}</h1>
                <p class="text-gray-600 mt-1">{{ Auth::user()->email }}</p>
                <div class="flex items-center space-x-3 mt-2">

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ is_object(Auth::user()->status) ? Auth::user()->status->value : Auth::user()->status }}
                    </span>
                    
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 capitalize">
                        {{ is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role }}
                    </span>

                </div>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
            <p class="text-sm text-gray-600 mt-1">Update your personal details and contact information.</p>
        </div>
        
        <form wire:submit.prevent="updateProfile" class="p-6 space-y-6">
            @if (session()->has('profile_updated'))
                <div x-data="{ show: true }" x-show="show" x-transition 
                     class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('profile_updated') }}
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            @php
                $canUpdateName = Auth::user()->can('updateName', Auth::user());
            @endphp
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="first_name" class="block text-sm font-medium text-gray-700">
                        First Name
                        @if(!$canUpdateName)
                            <span class="text-xs text-green-600 ml-1">(Verified - Cannot be changed)</span>
                        @endif
                    </label>
                    <input type="text" id="first_name" wire:model="first_name" 
                           @if(!$canUpdateName) disabled @endif
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ !$canUpdateName ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                    @error('first_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="last_name" class="block text-sm font-medium text-gray-700">
                        Last Name
                        @if(!$canUpdateName)
                            <span class="text-xs text-green-600 ml-1">(Verified - Cannot be changed)</span>
                        @endif
                    </label>
                    <input type="text" id="last_name" wire:model="last_name" 
                           @if(!$canUpdateName) disabled @endif
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ !$canUpdateName ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                    @error('last_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            @php
                $isVerified = Auth::user()->isEmailVerified() || Auth::user()->isPhoneVerified();
            @endphp

            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email Address
                    @if($isVerified)
                        <span class="text-xs text-green-600 ml-1">(Verified - Cannot be changed)</span>
                    @endif
                </label>
                <input type="email" id="email" wire:model="email" 
                       @if($isVerified) disabled @endif
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ $isVerified ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="phone_number" class="block text-sm font-medium text-gray-700">
                    Phone Number
                    @if($isVerified)
                        <span class="text-xs text-green-600 ml-1">(Verified - Cannot be changed)</span>
                    @endif
                </label>
                <input type="text" id="phone_number" wire:model="phone_number" 
                       @if($isVerified) disabled @endif
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors {{ $isVerified ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                @error('phone_number') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Location & Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" id="city" wire:model="city" placeholder="e.g. Lagos, Nigeria"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('city') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">Date of Birth</label>
                    <input type="date" id="date_of_birth" wire:model="date_of_birth"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('date_of_birth') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Professional Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="occupation" class="block text-sm font-medium text-gray-700">Current Occupation</label>
                    <input type="text" id="occupation" wire:model="occupation" placeholder="e.g. Software Developer"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('occupation') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="employment_status" class="block text-sm font-medium text-gray-700">Employment Status</label>
                    <select id="employment_status" wire:model="employment_status"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Select Status</option>
                        <option value="employed">Employed</option>
                        <option value="self_employed">Self Employed</option>
                        <option value="unemployed">Unemployed</option>
                        <option value="student">Student</option>
                        <option value="retired">Retired</option>
                    </select>
                    @error('employment_status') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Education -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="highest_qualification" class="block text-sm font-medium text-gray-700">Highest Qualification</label>
                    <select id="highest_qualification" wire:model="highest_qualification"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <option value="">Select Qualification</option>
                        <option value="secondary">Secondary School</option>
                        <option value="diploma">Diploma/Certificate</option>
                        <option value="bachelor">Bachelor's Degree</option>
                        <option value="master">Master's Degree</option>
                        <option value="phd">PhD/Doctorate</option>
                        <option value="professional">Professional Certification</option>
                    </select>
                    @error('highest_qualification') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="field_of_study" class="block text-sm font-medium text-gray-700">Field of Study</label>
                    <input type="text" id="field_of_study" wire:model="field_of_study" placeholder="e.g. Computer Science"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('field_of_study') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- About & Bio -->
            <div class="space-y-2">
                <label for="about_me" class="block text-sm font-medium text-gray-700">About Me (Brief)</label>
                <textarea id="about_me" wire:model="about_me" rows="3" 
                          placeholder="Brief introduction about yourself..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 300 characters - appears in directory</p>
                @error('about_me') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="bio" class="block text-sm font-medium text-gray-700">Full Bio (Optional)</label>
                <textarea id="bio" wire:model="bio" rows="5" 
                          placeholder="Detailed biography, achievements, interests, and background..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
                <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters - appears on full profile</p>
                @error('bio') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="skills" class="block text-sm font-medium text-gray-700">Skills & Expertise</label>
                <input type="text" id="skills" wire:model="skills" placeholder="e.g. Leadership, Public Speaking, Project Management"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <p class="text-xs text-gray-500 mt-1">Separate skills with commas</p>
                @error('skills') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Social Media -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="linkedin_handle" class="block text-sm font-medium text-gray-700">LinkedIn Profile</label>
                    <input type="text" id="linkedin_handle" wire:model="linkedin_handle" placeholder="linkedin.com/in/username"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('linkedin_handle') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="space-y-2">
                    <label for="twitter_handle" class="block text-sm font-medium text-gray-700">Twitter Handle</label>
                    <input type="text" id="twitter_handle" wire:model="twitter_handle" placeholder="@username"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    @error('twitter_handle') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="bg-gray-50 rounded-lg p-4 sm:p-6 space-y-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-900">Privacy Settings</h3>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                    <div class="flex-1">
                        <label class="text-sm sm:text-base font-medium text-gray-700">Public Profile</label>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Allow your profile to appear in the public members directory</p>
                    </div>
                    <div class="shrink-0">
                        <input type="checkbox" wire:model="is_public" class="w-6 h-6 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                    <div class="flex-1">
                        <label class="text-sm sm:text-base font-medium text-gray-700">Show Email Publicly</label>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Display your email address on your public profile</p>
                    </div>
                    <div class="shrink-0">
                        <input type="checkbox" wire:model="email_public" class="w-6 h-6 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    </div>
                </div>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0">
                    <div class="flex-1">
                        <label class="text-sm sm:text-base font-medium text-gray-700">Show Phone Publicly</label>
                        <p class="text-xs sm:text-sm text-gray-500 mt-1">Display your phone number on your public profile</p>
                    </div>
                    <div class="shrink-0">
                        <input type="checkbox" wire:model="phone_public" class="w-6 h-6 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Security Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Security</h2>
            <p class="text-sm text-gray-600 mt-1">Manage your password and security settings.</p>
        </div>
        
        <form wire:submit.prevent="updatePassword" class="p-6 space-y-6">
            {{-- Hidden username field for password manager accessibility --}}
            <input type="email" name="username" value="{{ Auth::user()->email }}" autocomplete="username" style="display: none;">

            @if (session()->has('password_updated'))
                <div x-data="{ show: true }" x-show="show" x-transition
                     class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        {{ session('password_updated') }}
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            @endif

            <div class="space-y-2">
                <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" id="current_password" wire:model="current_password"
                       autocomplete="current-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                @error('current_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ showPassword: false }" class="space-y-2">
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <div class="relative">
                    <input :type="showPassword ? 'text' : 'password'" id="new_password" wire:model="new_password"
                           autocomplete="new-password"
                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        </svg>
                    </button>
                </div>
                @error('new_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="new_password_confirmation" wire:model="new_password_confirmation"
                       autocomplete="new-password"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" 
                        class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <!-- Account Overview -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Overview</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl">
                <div class="text-2xl font-bold text-green-700">{{ is_object(Auth::user()->status) ? Auth::user()->status->value : Auth::user()->status }}</div>
                <div class="text-sm text-green-600 mt-1">Account Status</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl">
                <div class="text-2xl font-bold text-blue-700 capitalize">{{ is_object(Auth::user()->role) ? Auth::user()->role->value : Auth::user()->role }}</div>
                <div class="text-sm text-blue-600 mt-1">Role</div>
            </div>
            <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl">
                <div class="text-2xl font-bold text-purple-700">{{ Auth::user()->created_at->format('M Y') }}</div>
                <div class="text-sm text-purple-600 mt-1">Member Since</div>
            </div>
        </div>
    </div>
</div>