<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Create New User</h1>
                <p class="text-sm text-gray-500">Add a new user to the system</p>
            </div>
        </div>
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
            <button wire:click="save" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Create User
            </button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                Cancel
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg border p-4 sm:p-6">
        <div class="space-y-6 sm:space-y-8">
            <!-- Role Selection -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Type</h3>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select wire:model.live="userData.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="voter">Voter</option>
                        </select>
                        @error('userData.role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex items-center">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 w-full">
                            <p class="text-sm text-blue-700">Default password: <code class="bg-blue-100 px-2 py-1 rounded text-xs">12345678</code></p>
                        </div>
                    </div>
                </div>
            </div>

            @if(in_array('basic', $this->getRoleFields()))
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                        <input wire:model="userData.first_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                        <input wire:model="userData.last_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input wire:model="userData.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @if(in_array($userData['role'], ['voter', 'admin']))
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input wire:model="userData.phone_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>
            </div>
            @endif

            @if(in_array('kyc', $this->getRoleFields()))
            <!-- KYC Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                        <input wire:model="userData.date_of_birth" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.date_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                        <select wire:model="userData.marital_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="divorced">Divorced</option>
                            <option value="widowed">Widowed</option>
                        </select>
                        @error('userData.marital_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('education', $this->getRoleFields()))
            <!-- Education & Career -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Education & Career</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Highest Qualification</label>
                        <input wire:model="userData.highest_qualification" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.highest_qualification') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Field of Study</label>
                        <input wire:model="userData.field_of_study" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.field_of_study') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Student Status</label>
                        <select wire:model="userData.student_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="current_student">Current Student</option>
                            <option value="graduate">Graduate</option>
                            <option value="dropout">Dropout</option>
                        </select>
                        @error('userData.student_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                        <select wire:model="userData.employment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Status</option>
                            <option value="employed">Employed</option>
                            <option value="unemployed">Unemployed</option>
                            <option value="self_employed">Self Employed</option>
                            <option value="student">Student</option>
                        </select>
                        @error('userData.employment_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('location', $this->getRoleFields()))
            <!-- Location & Work -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Location & Work</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <select wire:model="userData.location_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Location</option>
                            <option value="home">Home Based</option>
                            <option value="abroad">Abroad</option>
                        </select>
                        @error('userData.location_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">City (if Abroad)</label>
                        <input wire:model="userData.abroad_city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.abroad_city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Occupation</label>
                        <input wire:model="userData.current_occupation" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('userData.current_occupation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            @endif

            @if(in_array('skills', $this->getRoleFields()))
            <!-- Skills -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Skills & Expertise</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                    <textarea wire:model="userData.skills" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="List skills separated by commas"></textarea>
                    @error('userData.skills') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            @endif
        </div>
    </div>
</div>