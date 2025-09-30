<div>
    <!-- Header with Stats -->
    <div class="mb-6">
        <div class="flex flex-col mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Admin Management</h2>
                <p class="text-gray-600 mt-1">Manage system administrators and their permissions</p>
            </div>
            <div class="flex space-x-2 mt-4">
                <button wire:click="exportAdmins" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Export Admins
                </button>
                <button wire:click="openCreateModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add New Admin
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-500">Total Admins</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $users->count() }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-500">Pending</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $users->where('status', 'pending')->count() }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-500">Active</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $users->where('status', 'approved')->count() }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-500">Suspended</span>
                </div>
                <div class="text-2xl font-bold text-gray-900">{{ $users->where('status', 'suspended')->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <input wire:model.live="search" type="text" placeholder="Search admins..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select wire:model.live="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All Types</option>
                    <option value="admin">Admins</option>
                    <option value="observer">Observers</option>
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All Status</option>
                    <option value="approved">Active</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
            <div>
                <button wire:click="resetFilters" class="w-full px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Admin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium text-sm">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900">••••••••</div>
                        <div class="text-sm text-gray-500">Protected</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($user->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $user->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($user->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($user->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                            {{ $user->status === 'approved' ? 'Active' : ucfirst(is_object($user->status) ? $user->status->value : $user->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($user->created_at)->format('M j, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button type="button" wire:click="viewAdmin({{ $user->id }})" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No admins found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="lg:hidden space-y-3">
        @forelse($users as $user)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-sm">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full 
                    {{ $user->status === 'approved' ? 'bg-green-100 text-green-800' : 
                       ($user->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                       ($user->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                    {{ $user->status === 'approved' ? 'Active' : ucfirst(is_object($user->status) ? $user->status->value : $user->status) }}
                </span>
            </div>
            
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Type:</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ ucfirst($user->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Created:</span>
                    <span class="text-gray-900">{{ \Carbon\Carbon::parse($user->created_at)->format('M j, Y') }}</span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button type="button" wire:click="viewAdmin({{ $user->id }})" class="w-full px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">View Details</button>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
            <p class="text-gray-500">No admins found.</p>
        </div>
        @endforelse
    </div>

    @if($showCreateModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $editingAdmin ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 6v6m0 0v6m0-6h6m-6 0H6' }}"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $editingAdmin ? 'Edit' : 'Add New' }} {{ ucfirst($user_type) }}</h3>
                            <p class="text-sm text-gray-500">{{ $editingAdmin ? 'Update' : 'Create' }} {{ $user_type }} account</p>
                        </div>
                    </div>
                    <button wire:click="closeModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Form -->
            <form wire:submit="save" class="px-6 py-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Account Type</label>
                        <select wire:model="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="admin">Administrator</option>
                            <option value="observer">Observer</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input wire:model="first_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('first_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input wire:model="last_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            @error('last_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input wire:model="email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input wire:model="phone_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('phone_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    @if(!$editingAdmin)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input wire:model="password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input wire:model="password_confirmation" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    @endif
                </div>
            </form>
            
            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeModal" type="button" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="save" type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ $editingAdmin ? 'Update' : 'Create' }} {{ ucfirst($user_type) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Admin Details Modal -->
    @if($showAdminModal && $selectedAdmin)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeAdminModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full" style="height: 500px; max-height: 500px; overflow-y: auto;" wire:click.stop>
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-lg">{{ substr($selectedAdmin->first_name, 0, 1) }}{{ substr($selectedAdmin->last_name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $selectedAdmin->first_name }} {{ $selectedAdmin->last_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $selectedAdmin instanceof \App\Models\Admin ? 'Administrator' : 'Observer' }} Profile</p>
                        </div>
                    </div>
                    <button wire:click="closeAdminModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-6 py-6">
                <div class="space-y-6">
                    <!-- Contact Information -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Contact Information</h4>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedAdmin->email }}</p>
                                    <p class="text-xs text-gray-500">Email Address</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedAdmin->phone_number ?? 'Not provided' }}</p>
                                    <p class="text-xs text-gray-500">Phone Number</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Account Details</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Type</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-blue-100 text-blue-800">{{ $selectedAdmin instanceof \App\Models\Admin ? 'Administrator' : 'Observer' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Status</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $selectedAdmin->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($selectedAdmin->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($selectedAdmin->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ $selectedAdmin->status === 'approved' ? 'Active' : ucfirst(is_object($selectedAdmin->status) ? $selectedAdmin->status->value : $selectedAdmin->status) }}
                                </span>
                            </div>
                            @if($selectedAdmin instanceof \App\Models\Admin && $selectedAdmin->permissions)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Permissions</span>
                                <span class="text-sm font-medium text-gray-900">{{ count($selectedAdmin->permissions) }} assigned</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedAdmin->created_at)->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex flex-wrap justify-end gap-3">
                    <button wire:click="edit({{ $selectedAdmin->id }}, '{{ $selectedAdmin instanceof \App\Models\Admin ? 'admin' : 'observer' }}')" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </button>
                    @if($selectedAdmin->status === 'approved')
                        <button wire:click="updateStatus({{ $selectedAdmin->id }}, '{{ $selectedAdmin instanceof \App\Models\Admin ? 'admin' : 'observer' }}', 'suspended')" class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                            Suspend
                        </button>
                    @elseif($selectedAdmin->status === 'suspended')
                        <button wire:click="updateStatus({{ $selectedAdmin->id }}, '{{ $selectedAdmin instanceof \App\Models\Admin ? 'admin' : 'observer' }}', 'approved')" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Activate
                        </button>
                    @elseif($selectedAdmin->status === 'pending')
                        <button wire:click="updateStatus({{ $selectedAdmin->id }}, '{{ $selectedAdmin instanceof \App\Models\Admin ? 'admin' : 'observer' }}', 'approved')" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Approve
                        </button>
                    @endif
                    <button wire:click="closeAdminModal" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>