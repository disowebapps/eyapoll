<div class="space-y-6">
    <!-- Profile Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center space-x-6">
            <div class="relative cursor-pointer" x-data="{}" @click="$refs.profileImageInput.click()">
                <img src="{{ Auth::guard('observer')->user()->profile_image_url }}" 
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
                <h1 class="text-2xl font-bold text-gray-900">{{ Auth::guard('observer')->user()->full_name }}</h1>
                <p class="text-gray-600 mt-1">{{ Auth::guard('observer')->user()->email }}</p>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 mt-2">
                    Observer
                </span>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Personal Information</h2>
        </div>
        
        <form wire:submit.prevent="updateProfile" class="p-6 space-y-6">
            @if (session()->has('profile_updated'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('profile_updated') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" wire:model="first_name" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('first_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" wire:model="last_name" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('last_name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" wire:model="email" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="text" wire:model="phone_number" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('phone_number') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <!-- Password Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Change Password</h2>
        </div>
        
        <form wire:submit.prevent="updatePassword" class="p-6 space-y-6">
            @if (session()->has('password_updated'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('password_updated') }}
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Current Password</label>
                <input type="password" wire:model="current_password" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('current_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" wire:model="new_password" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                @error('new_password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" wire:model="new_password_confirmation" class="mt-1 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>