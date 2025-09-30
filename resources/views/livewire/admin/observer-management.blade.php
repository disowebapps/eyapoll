<div>
    <!-- Header with Stats -->
    <div class="mb-6">
        <div class="flex flex-col mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Observer Management</h2>
                <p class="text-gray-600 mt-1">Manage election observers and their assignments</p>
            </div>
            <div class="flex space-x-2 mt-4">
                <button wire:click="exportObservers" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Export Observers
                </button>
                <button wire:click="addObserver" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add Observer
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Observers</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Approved</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Rejected</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Suspended</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['suspended'] }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Revoked</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['revoked'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <input wire:model.live="search" type="text" placeholder="Search observers..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <select wire:model.live="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All Types</option>
                    <option value="organization">Organization</option>
                    <option value="independent">Independent</option>
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="all">All Status</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                    <option value="revoked">Revoked</option>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Observer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Privileges</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registered</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($observers as $observer)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-medium text-sm">{{ substr($observer->first_name, 0, 1) }}{{ substr($observer->last_name, 0, 1) }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="font-medium text-gray-900">{{ $observer->first_name }} {{ $observer->last_name }}</div>

                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900">••••••••</div>
                        <div class="text-sm text-gray-500">Protected</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $observer->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($observer->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($observer->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 
                               ($observer->status === 'revoked' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))) }}">
                            {{ ucfirst(is_string($observer->status) ? $observer->status : $observer->status->value) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            {{ count($observer->observer_privileges ?? []) }} privileges
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $observer->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button type="button" wire:click="viewObserver({{ $observer->id }})" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No observers found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="lg:hidden space-y-3">
        @forelse($observers as $observer)
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-sm">{{ substr($observer->first_name, 0, 1) }}{{ substr($observer->last_name, 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $observer->first_name }} {{ $observer->last_name }}</div>

                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-medium rounded-full 
                    {{ $observer->status === 'approved' ? 'bg-green-100 text-green-800' : 
                       ($observer->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                       ($observer->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 
                       ($observer->status === 'revoked' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))) }}">
                    {{ ucfirst(is_string($observer->status) ? $observer->status : $observer->status->value) }}
                </span>
            </div>
            
            <div class="space-y-2 text-sm mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Type:</span>
                    <span class="text-gray-900">{{ ucfirst($observer->type) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Privileges:</span>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ count($observer->observer_privileges ?? []) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Registered:</span>
                    <span class="text-gray-900">{{ $observer->created_at->format('M j, Y') }}</span>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button type="button" wire:click="viewObserver({{ $observer->id }})" class="w-full px-3 py-2 text-sm bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">View Details</button>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
            <p class="text-gray-500">No observers found.</p>
        </div>
        @endforelse
    </div>

    <!-- Observer Details Modal -->
    @if($showObserverModal && $selectedObserver)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeObserverModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full" style="height: 500px; max-height: 500px; overflow-y: auto;" wire:click.stop>
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-lg">{{ substr($selectedObserver->first_name, 0, 1) }}{{ substr($selectedObserver->last_name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">{{ $selectedObserver->first_name }} {{ $selectedObserver->last_name }}</h3>
                            <p class="text-sm text-gray-500">Observer Profile</p>
                        </div>
                    </div>
                    <button wire:click="closeObserverModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
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
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedObserver->email }}</p>
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
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedObserver->phone_number ?? 'Not provided' }}</p>
                                    <p class="text-xs text-gray-500">Phone Number</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Observer Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Observer Details</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Type</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-indigo-100 text-indigo-800">{{ ucfirst($selectedObserver->type) }}</span>
                            </div>
                            @if($selectedObserver->type === 'organization')
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Organization</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedObserver->organization_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Address</span>
                                <span class="text-sm font-medium text-gray-900 text-right">{{ $selectedObserver->organization_address ?? 'N/A' }}</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Certification</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedObserver->certification_number ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Status</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $selectedObserver->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($selectedObserver->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($selectedObserver->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 
                                       ($selectedObserver->status === 'revoked' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))) }}">
                                    {{ ucfirst(is_string($selectedObserver->status) ? $selectedObserver->status : $selectedObserver->status->value) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Privileges</span>
                                <span class="text-sm font-medium text-gray-900">{{ count($selectedObserver->observer_privileges ?? []) }} assigned</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedObserver->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex flex-wrap justify-end gap-3">
                    @if($selectedObserver->status === 'pending')
                        <button wire:click="approveObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Approve
                        </button>
                        <button wire:click="rejectObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject
                        </button>
                    @elseif($selectedObserver->status === 'approved')
                        <button wire:click="openPrivilegeModal({{ $selectedObserver->id }})" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Manage Privileges
                        </button>
                        <button wire:click="openAssignModal({{ $selectedObserver->id }})" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                            Assign Elections
                        </button>
                        <button wire:click="suspendObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                            Suspend
                        </button>
                    @elseif($selectedObserver->status === 'suspended')
                        <button wire:click="unsuspendObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Unsuspend
                        </button>
                        <button wire:click="revokeObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            Revoke Access
                        </button>
                    @elseif($selectedObserver->status === 'revoked')
                        <button wire:click="unrevokeObserver({{ $selectedObserver->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Restore Access
                        </button>
                    @endif
                    <button wire:click="closeObserverModal" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Privilege Management Modal -->
    @if($showPrivilegeModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Manage Observer Privileges</h3>
            
            <form wire:submit="updatePrivileges" class="flex flex-col flex-1">
                <div class="flex-1 overflow-y-auto space-y-3 mb-6">
                    @foreach($availablePrivileges as $privilege)
                    <label class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" wire:model="selectedPrivileges" value="{{ $privilege }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $privilege)) }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $privilege }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" wire:click="closePrivilegeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors duration-200">Update Privileges</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Assignment Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Elections</h3>
            
            <form wire:submit="updateElectionAssignments" class="flex flex-col flex-1">
                <div class="flex-1 overflow-y-auto space-y-3 mb-6">
                    @foreach($elections as $election)
                    <label class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" wire:model="selectedElections" value="{{ $election->id }}" class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900">{{ $election->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $election->type->label() }} • {{ $election->starts_at->format('M j, Y') }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
                
                <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" wire:click="closeAssignModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors duration-200">Update Assignments</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Create Observer Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeCreateModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-xl font-semibold text-gray-900">Create New Observer</h3>
            </div>
            
            <form wire:submit.prevent="createObserver" class="px-6 py-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input wire:model="newObserver.first_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('newObserver.first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input wire:model="newObserver.last_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('newObserver.last_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input wire:model="newObserver.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('newObserver.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input wire:model="newObserver.phone_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('newObserver.phone_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select wire:model="newObserver.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="independent">Independent</option>
                            <option value="organization">Organization</option>
                        </select>
                        @error('newObserver.type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Organization Name (Optional)</label>
                        <input wire:model="newObserver.organization_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('newObserver.organization_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input wire:model="newObserver.password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('newObserver.password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observer Privileges</label>
                        <div class="space-y-2 max-h-32 overflow-y-auto border border-gray-200 rounded-lg p-3">
                            @foreach($availablePrivileges as $privilege)
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" wire:model="newObserver.privileges" value="{{ $privilege }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $privilege)) }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('newObserver.privileges') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </form>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeCreateModal" type="button" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button wire:click="createObserver" type="button" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        Create Observer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>