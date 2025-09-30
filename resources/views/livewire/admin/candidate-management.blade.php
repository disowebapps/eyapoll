<div class="space-y-4 sm:space-y-6">
    <!-- Header with Stats -->
    <div class="space-y-4 sm:space-y-6">
        <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Candidate Management</h2>
                <p class="text-gray-600 mt-1">Manage candidate applications and approvals</p>
            </div>
            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                <button wire:click="exportCandidates" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Export Candidates
                </button>
                <button wire:click="addCandidate" class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add Candidate
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
                <div class="flex items-center mb-2 sm:mb-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-gray-500">Total</span>
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
                <div class="flex items-center mb-2 sm:mb-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-gray-500">Pending</span>
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
                <div class="flex items-center mb-2 sm:mb-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-gray-500">Approved</span>
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['approved'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4">
                <div class="flex items-center mb-2 sm:mb-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-gray-500">Rejected</span>
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['rejected'] }}</div>
            </div>
            <div class="bg-white rounded-lg border border-gray-200 p-3 sm:p-4 col-span-2 sm:col-span-1">
                <div class="flex items-center mb-2 sm:mb-3">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                        </svg>
                    </div>
                    <span class="ml-2 sm:ml-3 text-xs sm:text-sm font-medium text-gray-500">Suspended</span>
                </div>
                <div class="text-lg sm:text-2xl font-bold text-gray-900">{{ $stats['suspended'] }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 sm:p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <input wire:model.live="search" type="text" placeholder="Search candidates..." class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            
            <select wire:model.live="statusFilter" class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="suspended">Suspended</option>
            </select>
            
            <select wire:model.live="electionFilter" class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="all">All Elections</option>
                @foreach($elections as $election)
                    <option value="{{ $election->id }}">{{ $election->title }}</option>
                @endforeach
            </select>
            
            <select wire:model.live="positionFilter" class="px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                <option value="all">All Positions</option>
                @foreach($positions as $position)
                    <option value="{{ $position->id }}">{{ $position->title }} ({{ $position->election->title }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($showBulkActions)
    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-3 sm:p-4">
        <div class="flex flex-col space-y-3 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <span class="text-sm text-indigo-700">{{ count($selectedCandidates) }} candidates selected</span>
            <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-2">
                <select wire:model="bulkAction" class="px-3 py-1 border border-indigo-300 rounded text-sm">
                    <option value="">Select Action</option>
                    <option value="approved">Approve</option>
                    <option value="rejected">Reject</option>
                    <option value="suspended">Suspend</option>
                </select>
                <div class="flex space-x-2">
                    <button wire:click="executeBulkAction" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700">Apply</button>
                    <button wire:click="clearSelection" class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-sm hover:bg-gray-400">Clear</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Election</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($candidates as $candidate)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold text-sm">{{ substr($candidate->user->first_name ?? 'U', 0, 1) }}{{ substr($candidate->user->last_name ?? 'N', 0, 1) }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $candidate->user->first_name ?? 'Unknown' }} {{ $candidate->user->last_name ?? 'User' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-gray-900">••••••••</div>
                        <div class="text-sm text-gray-500">Protected</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $candidate->election->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $candidate->position->title ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($candidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($candidate->status->value === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ ucfirst($candidate->status->value) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $candidate->created_at->format('M j, Y g:i A') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex space-x-2">
                            <button type="button" wire:click="viewCandidate({{ $candidate->id }})" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200">View</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $candidates->links() }}
        </div>
    </div>

    <!-- Mobile Cards -->
    <div class="lg:hidden space-y-3 sm:space-y-4">
        @foreach($candidates as $candidate)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-3 sm:p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ substr($candidate->user->first_name ?? 'U', 0, 1) }}{{ substr($candidate->user->last_name ?? 'N', 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">{{ $candidate->user->first_name ?? 'Unknown' }} {{ $candidate->user->last_name ?? 'User' }}</h3>
                            <p class="text-sm text-gray-500">{{ $candidate->position->title ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : 
                           ($candidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                           ($candidate->status->value === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ ucfirst($candidate->status->value) }}
                    </span>
                </div>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-900 font-medium">{{ $candidate->election->title ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Applied:</span>
                        <span class="text-gray-900">{{ $candidate->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="px-3 sm:px-4 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex space-x-2">
                    <button type="button" wire:click="viewCandidate({{ $candidate->id }})" 
                            class="flex-1 px-3 py-2 text-sm font-medium text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100">
                        Quick View
                    </button>
                    <a href="{{ route('admin.candidates.show', $candidate->id) }}" 
                       class="flex-1 px-3 py-2 text-sm font-medium text-center text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        Full Details
                    </a>
                </div>
            </div>
        </div>
        @endforeach
        
        <div class="mt-4 sm:mt-6">
            {{ $candidates->links() }}
        </div>
    </div>

    <!-- Candidate Details Modal -->
    @if($showCandidateModal && $selectedCandidate)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" wire:click="closeCandidateModal">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto" wire:click.stop>
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm sm:text-lg">{{ substr($selectedCandidate->user->first_name ?? 'U', 0, 1) }}{{ substr($selectedCandidate->user->last_name ?? 'N', 0, 1) }}</span>
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $selectedCandidate->user->first_name ?? 'Unknown' }} {{ $selectedCandidate->user->last_name ?? 'User' }}</h3>
                            <p class="text-sm text-gray-500">Candidate Profile</p>
                        </div>
                    </div>
                    <button wire:click="closeCandidateModal" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="px-4 sm:px-6 py-4 sm:py-6">
                <div class="space-y-4 sm:space-y-6">
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
                                    <p class="text-sm font-medium text-gray-900 break-all">{{ $selectedCandidate->user->email ?? 'No email' }}</p>
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
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedCandidate->user->phone_number ?? 'Not provided' }}</p>
                                    <p class="text-xs text-gray-500">Phone Number</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Candidate Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Candidate Details</h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Election</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedCandidate->election->title ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Position</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedCandidate->position->title ?? 'N/A' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Status</span>
                                <span class="px-3 py-1 text-sm font-medium rounded-full 
                                    {{ $selectedCandidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($selectedCandidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($selectedCandidate->status->value === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
                                    {{ ucfirst($selectedCandidate->status->value) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Applied</span>
                                <span class="text-sm font-medium text-gray-900">{{ $selectedCandidate->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="px-4 sm:px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl">
                <div class="flex flex-col space-y-2 sm:flex-row sm:flex-wrap sm:justify-end sm:space-y-0 sm:gap-3">
                    @if($selectedCandidate->status->value === 'pending')
                        <button wire:click="approveCandidate({{ $selectedCandidate->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Approve
                        </button>
                        <button wire:click="rejectCandidate({{ $selectedCandidate->id }})" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject
                        </button>
                    @elseif($selectedCandidate->status->value === 'approved')
                        <button wire:click="suspendCandidate({{ $selectedCandidate->id }})" class="px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                            Suspend
                        </button>
                    @elseif($selectedCandidate->status->value === 'suspended')
                        <button wire:click="unsuspendCandidate({{ $selectedCandidate->id }})" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                            Unsuspend
                        </button>
                    @endif
                    <button wire:click="closeCandidateModal" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif


</div>