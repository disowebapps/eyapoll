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
                <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">
                    @if($userType === 'candidate' && isset($user->user))
                        {{ $user->user->first_name }} {{ $user->user->last_name }}
                    @else
                        {{ $user->first_name }} {{ $user->last_name }}
                    @endif
                </h1>
                <p class="text-sm text-gray-500">{{ ucfirst($userType) }} Management</p>
            </div>
        </div>
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
            @if(!$editing)
                <button wire:click="startEdit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </button>
                @if($userType === 'user' && (is_string($user->status) ? $user->status : $user->status->value) === 'approved')
                    <button wire:click="accreditUser" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Accredit Voter</span>
                        <span class="sm:hidden">Accredit</span>
                    </button>
                @endif
                @php
                    $currentStatus = ($userType === 'candidate' && isset($user->status) && is_object($user->status)) ? $user->status->value : (is_string($user->status) ? $user->status : $user->status->value);
                @endphp
                @if($currentStatus === 'suspended')
                    <button wire:click="unsuspendUser" wire:confirm="Are you sure you want to unsuspend this user?" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Unsuspend
                    </button>
                @else
                    <button wire:click="suspendUser" wire:confirm="Are you sure you want to suspend this user?" class="inline-flex items-center justify-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                        Suspend
                    </button>
                @endif
            @else
                <button wire:click="save" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save
                </button>
                <button wire:click="cancelEdit" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Cancel
                </button>
            @endif
        </div>
    </div>

    <!-- User Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Profile Card -->
        <div class="bg-white rounded-lg border p-4 sm:p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-10 h-10 sm:w-20 sm:h-20 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-lg sm:text-xl font-semibold text-blue-600">
                        @if($userType === 'candidate' && isset($user->user))
                            {{ substr($user->user->first_name, 0, 1) }}{{ substr($user->user->last_name, 0, 1) }}
                        @else
                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                        @endif
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">
                        @if($userType === 'candidate' && isset($user->user))
                            {{ $user->user->first_name }} {{ $user->user->last_name }}
                        @else
                            {{ $user->first_name }} {{ $user->last_name }}
                        @endif
                    </h3>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $status = ($userType === 'candidate' && isset($user->status) && is_object($user->status)) ? $user->status->value : (is_string($user->status) ? $user->status : $user->status->value);
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'approved' => 'bg-blue-100 text-blue-800',
                        'accredited' => 'bg-green-100 text-green-800',
                        'rejected' => 'bg-red-100 text-red-800',
                        'suspended' => 'bg-gray-100 text-gray-800'
                    ];
                    $statusColor = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="px-3 py-1 text-sm font-medium rounded-full {{ $statusColor }}">
                    {{ ucfirst($status) }}
                    @if($status === 'accredited')
                        <svg class="w-3 h-3 inline ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </span>
                <span class="px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                    {{ ucfirst($userType) }}
                </span>
            </div>
        </div>

        <!-- Details Form -->
        <div class="lg:col-span-2 bg-white rounded-lg border p-4 sm:p-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-6">User Information</h4>
            
            @if($editing)
                <div class="space-y-6 sm:space-y-8">
                    <!-- Basic Information -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-md font-medium text-gray-900">Basic Information</h5>
                            <button wire:click="saveBasicInfo" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                <input wire:model="editData.first_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                <input wire:model="editData.last_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2 lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                                <input wire:model="editData.date_of_birth" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.date_of_birth') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2 lg:col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input wire:model="editData.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input wire:model="editData.phone_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                                <select wire:model="editData.marital_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                                @error('editData.marital_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-md font-medium text-gray-900">Account Settings</h5>
                            <button wire:click="saveAccountSettings" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                <select wire:model="editData.role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="voter">Voter</option>
                                    <option value="candidate">Candidate</option>
                                    <option value="observer">Observer</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('editData.role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="editData.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="accredited">Accredited</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                @error('editData.status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Education & Career -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-md font-medium text-gray-900">Education & Career</h5>
                            <button wire:click="saveEducationCareer" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Highest Qualification</label>
                                <input wire:model="editData.highest_qualification" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.highest_qualification') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Field of Study</label>
                                <input wire:model="editData.field_of_study" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.field_of_study') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Student Status</label>
                                <select wire:model="editData.student_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Status</option>
                                    <option value="current_student">Current Student</option>
                                    <option value="graduate">Graduate</option>
                                    <option value="dropout">Dropout</option>
                                </select>
                                @error('editData.student_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Employment Status</label>
                                <select wire:model="editData.employment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Status</option>
                                    <option value="employed">Employed</option>
                                    <option value="unemployed">Unemployed</option>
                                    <option value="self_employed">Self Employed</option>
                                    <option value="student">Student</option>
                                </select>
                                @error('editData.employment_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Occupation</label>
                                <input wire:model="editData.current_occupation" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.current_occupation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
                                <textarea wire:model="editData.skills" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="List skills separated by commas"></textarea>
                                @error('editData.skills') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location & Work -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-md font-medium text-gray-900">Location</h5>
                            <button wire:click="saveLocationWork" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                                Save
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                <select wire:model="editData.location_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Location</option>
                                    <option value="home">Home Based</option>
                                    <option value="abroad">Abroad</option>
                                </select>
                                @error('editData.location_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City (if Abroad)</label>
                                <input wire:model="editData.abroad_city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @error('editData.abroad_city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Executive Information -->
                    <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                        <div class="flex items-center justify-between mb-4">
                            <h5 class="text-md font-medium text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                                Executive Committee
                            </h5>
                            <button wire:click="saveExecutiveInfo" class="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700">
                                Save
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="flex items-center space-x-2">
                                    <input wire:model="editData.is_executive" type="checkbox" class="rounded border-gray-300 text-yellow-600 focus:ring-yellow-500">
                                    <span class="text-sm font-medium text-gray-700">Mark as Executive Committee Member</span>
                                </label>
                            </div>
                            @if($editData['is_executive'])
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Position Title</label>
                                    <input wire:model="editData.current_position" type="text" placeholder="e.g., President, Secretary, Treasurer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    @error('editData.current_position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                                    <input wire:model="editData.executive_order" type="number" min="1" max="100" placeholder="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    @error('editData.executive_order') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Lower numbers appear first</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term Start</label>
                                    <input wire:model="editData.term_start" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    @error('editData.term_start') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Term End</label>
                                    <input wire:model="editData.term_end" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                    @error('editData.term_end') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">First Name</label>
                        <p class="text-gray-900">
                            @if($userType === 'candidate' && isset($user->user))
                                {{ $user->user->first_name }}
                            @else
                                {{ $user->first_name }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Last Name</label>
                        <p class="text-gray-900">
                            @if($userType === 'candidate' && isset($user->user))
                                {{ $user->user->last_name }}
                            @else
                                {{ $user->last_name }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
                        <p class="text-gray-900 break-all">
                            @if($userType === 'candidate' && isset($user->user))
                                {{ $user->user->email }}
                            @else
                                {{ $user->email }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Phone Number</label>
                        <p class="text-gray-900">
                            @if($userType === 'candidate' && isset($user->user))
                                {{ $user->user->phone_number ?? 'Not provided' }}
                            @else
                                {{ $user->phone_number ?? 'Not provided' }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">User Type</label>
                        <p class="text-gray-900">{{ ucfirst($userType) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Member Since</label>
                        <p class="text-gray-900">{{ $user->created_at->format('M j, Y') }}</p>
                    </div>
                    @if($user->is_executive)
                        <div class="sm:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center mb-2">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                                <span class="font-medium text-yellow-800">Executive Committee Member</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                @if($user->current_position)
                                    <div>
                                        <span class="text-gray-600">Position:</span>
                                        <span class="font-medium text-gray-900">{{ $user->current_position }}</span>
                                    </div>
                                @endif
                                @if($user->term_start || $user->term_end)
                                    <div>
                                        <span class="text-gray-600">Term:</span>
                                        <span class="font-medium text-gray-900">
                                            {{ $user->term_start ? $user->term_start->format('M Y') : 'Current' }} - 
                                            {{ $user->term_end ? $user->term_end->format('M Y') : 'Present' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Activity Log -->
    <div class="bg-white rounded-lg border p-4 sm:p-6">
        <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h4>
        <div class="space-y-3">
            <div class="flex items-center space-x-3 text-sm">
                <div class="w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></div>
                <span class="text-gray-600">Account created</span>
                <span class="text-gray-400">{{ $user->created_at->diffForHumans() }}</span>
            </div>
            @if($user->updated_at != $user->created_at)
                <div class="flex items-center space-x-3 text-sm">
                    <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0"></div>
                    <span class="text-gray-600">Profile updated</span>
                    <span class="text-gray-400">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            @endif
        </div>
    </div>
</div>