<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border p-4 lg:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
            <div class="mb-4 lg:mb-0">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Token Management</h1>
                <p class="text-sm text-gray-600 mt-1">Issue, revoke, and manage vote tokens for elections</p>
            </div>
        </div>
        
        <!-- Election Filter -->
        <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Election</label>
            <select wire:model.live="selectedElection" class="w-full lg:w-1/3 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                <option value="">Select Election</option>
                @foreach($elections as $election)
                    <option value="{{ $election->id }}">{{ $election->title }}</option>
                @endforeach
            </select>
            @error('selectedElection') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div wire:click="setViewMode('approved')" class="bg-blue-50 rounded-lg p-4 text-center cursor-pointer hover:bg-blue-100 transition-colors {{ $viewMode === 'approved' ? 'ring-2 ring-blue-500' : '' }}">
                <div class="text-2xl lg:text-3xl font-bold text-blue-600">{{ $stats['total_approved'] }}</div>
                <div class="text-xs lg:text-sm text-blue-700 font-medium">Approved</div>
            </div>
            <div wire:click="setViewMode('accredited')" class="bg-green-50 rounded-lg p-4 text-center cursor-pointer hover:bg-green-100 transition-colors {{ $viewMode === 'accredited' ? 'ring-2 ring-green-500' : '' }}">
                <div class="text-2xl lg:text-3xl font-bold text-green-600">{{ $stats['total_accredited'] }}</div>
                <div class="text-xs lg:text-sm text-green-700 font-medium">Accredited</div>
            </div>
            <div wire:click="setViewMode('eligible')" class="bg-purple-50 rounded-lg p-4 text-center cursor-pointer hover:bg-purple-100 transition-colors {{ $viewMode === 'eligible' ? 'ring-2 ring-purple-500' : '' }}">
                <div class="text-2xl lg:text-3xl font-bold text-purple-600">{{ $stats['eligible_users'] }}</div>
                <div class="text-xs lg:text-sm text-purple-700 font-medium">Eligible</div>
            </div>
            <div wire:click="setViewMode('tokens')" class="bg-orange-50 rounded-lg p-4 text-center cursor-pointer hover:bg-orange-100 transition-colors {{ $viewMode === 'tokens' ? 'ring-2 ring-orange-500' : '' }}">
                <div class="text-2xl lg:text-3xl font-bold text-orange-600">{{ $stats['tokens_for_election'] }}</div>
                <div class="text-xs lg:text-sm text-orange-700 font-medium">Tokens Issued</div>
            </div>
        </div>

        <!-- Enhanced Metrics -->
        @if($selectedElection)
        <div class="mt-6 bg-gray-50 rounded-lg p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Detailed Metrics</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="text-sm font-medium text-gray-500 mb-1">Daily Token Issuances</div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['metrics']['daily_issuances'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Average per day</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="text-sm font-medium text-gray-500 mb-1">Daily Revocations</div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['metrics']['daily_revocations'] }}</div>
                    <div class="text-xs text-gray-500 mt-1">Average per day</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="text-sm font-medium text-gray-500 mb-1">Active Tokens</div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['metrics']['active_tokens_percent'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">Of eligible voters</div>
                </div>
                <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
                    <div class="text-sm font-medium text-gray-500 mb-1">Accreditation Rate</div>
                    <div class="text-xl font-bold text-gray-900">{{ $stats['metrics']['accreditation_rate'] }}%</div>
                    <div class="text-xs text-gray-500 mt-1">Of eligible voters</div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Controls -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search</label>
                <input wire:model.live="search" type="text" placeholder="Name or email..." 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex items-end">
                @if(!empty($selectedUsers))
                    <button wire:click="bulkAccreditUsers" wire:confirm="Accredit {{ count($selectedUsers) }} users?"
                            class="w-full lg:w-auto px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors flex items-center justify-center space-x-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Accredit ({{ count($selectedUsers) }})</span>
                    </button>
                @else
                    <button wire:click="setViewMode('all')" class="w-full lg:w-auto px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center justify-center space-x-2 font-medium {{ $viewMode === 'all' ? 'ring-2 ring-gray-500' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span>Show All</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Users Table -->
    @if($selectedElection)
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-4 lg:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Users & Tokens</h3>
                        @if($viewMode !== 'all')
                            <p class="text-sm text-gray-600">Showing: {{ ucfirst($viewMode) }} users</p>
                        @endif
                    </div>
                    <div class="flex items-center space-x-3">
                        <input wire:model.live="selectAll" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label class="text-sm font-medium text-gray-700">Select All</label>
                    </div>
                </div>
            </div>

            @if($users->count() > 0)
                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left">
                                    <input wire:model.live="selectAll" type="checkbox" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">User</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Token</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($users as $user)
                                <tr class="hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.users.show', $user->id) }}'">
                                    <td class="px-6 py-4" onclick="event.stopPropagation()">
                                        <input wire:model.live="selectedUsers" value="{{ $user->id }}" type="checkbox" 
                                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mr-4 shadow-sm">
                                                <span class="text-sm font-bold text-white">
                                                    {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</div>
                                                <div class="text-sm text-gray-500 break-all">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $user->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $existingToken = $user->voteTokens->where('election_id', $selectedElection)->first();
                                        @endphp
                                        
                                        @if($existingToken)
                                            <div class="flex flex-col space-y-1">
                                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $existingToken->is_used ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $existingToken->is_used ? 'Used' : 'Active' }}
                                                </span>
                                                <div class="text-xs text-gray-500">
                                                    Issued: {{ $existingToken->created_at->format('M j, g:i A') }}
                                                    @if($existingToken->is_used && $existingToken->updated_at != $existingToken->created_at)
                                                        <br>Used: {{ $existingToken->updated_at->format('M j, g:i A') }}
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                No Token
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                        @if($existingToken)
                                            <div class="flex items-center justify-end space-x-2">
                                                @if($existingToken->is_used)
                                                    <button wire:click="resetUsedToken({{ $existingToken->id }})" 
                                                            wire:confirm="Reset this used token?"
                                                            class="px-3 py-1 text-xs font-medium text-blue-700 bg-blue-100 rounded-md hover:bg-blue-200 transition-colors">
                                                        Reset
                                                    </button>
                                                @else
                                                    <button wire:click="revokeToken({{ $existingToken->id }})" 
                                                            wire:confirm="Revoke this token?"
                                                            class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-md hover:bg-red-200 transition-colors">
                                                        Revoke
                                                    </button>
                                                @endif
                                                <button wire:click="reissueToken({{ $existingToken->id }})" 
                                                        wire:confirm="Reissue this token?"
                                                        class="px-3 py-1 text-xs font-medium text-purple-700 bg-purple-100 rounded-md hover:bg-purple-200 transition-colors">
                                                    Reissue
                                                </button>
                                                <button wire:click="$set('reassignTokenId', {{ $existingToken->id }})" wire:click="$set('showReassignModal', true)" 
                                                        class="px-3 py-1 text-xs font-medium text-orange-700 bg-orange-100 rounded-md hover:bg-orange-200 transition-colors">
                                                    Reassign
                                                </button>
                                            </div>
                                        @else
                                            <button wire:click="openIssueModal({{ $user->id }})" 
                                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 transition-colors">
                                                Issue Token
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden space-y-4 p-4">
                    @foreach($users as $user)
                        @php
                            $existingToken = $user->voteTokens->where('election_id', $selectedElection)->first();
                        @endphp
                        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm cursor-pointer hover:shadow-md transition-shadow" onclick="window.location='{{ route('admin.users.show', $user->id) }}'">
                            <div class="flex items-start space-x-3 mb-4">
                                <input wire:model.live="selectedUsers" value="{{ $user->id }}" type="checkbox" 
                                       class="w-4 h-4 mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500" onclick="event.stopPropagation()">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-sm">
                                    <span class="text-xs font-bold text-white">
                                        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-sm text-gray-500 break-all">{{ $user->email }}</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $user->status->label() }}
                                    </span>
                                    @if($existingToken)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $existingToken->is_used ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $existingToken->is_used ? 'Used' : 'Active' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $user->created_at->format('M j, Y') }}
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-2" onclick="event.stopPropagation()">
                                @if($existingToken)
                                    @if($existingToken->is_used)
                                        <button wire:click="resetUsedToken({{ $existingToken->id }})" 
                                                wire:confirm="Reset this used token?"
                                                class="flex-1 px-3 py-2 text-xs font-medium text-blue-700 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                            Reset Token
                                        </button>
                                    @else
                                        <button wire:click="revokeToken({{ $existingToken->id }})" 
                                                wire:confirm="Revoke this token?"
                                                class="flex-1 px-3 py-2 text-xs font-medium text-red-700 bg-red-100 rounded-lg hover:bg-red-200 transition-colors">
                                            Revoke
                                        </button>
                                    @endif
                                    <button wire:click="reissueToken({{ $existingToken->id }})" 
                                            wire:confirm="Reissue this token?"
                                            class="flex-1 px-3 py-2 text-xs font-medium text-purple-700 bg-purple-100 rounded-lg hover:bg-purple-200 transition-colors">
                                        Reissue
                                    </button>
                                    <button wire:click="$set('reassignTokenId', {{ $existingToken->id }})" wire:click="$set('showReassignModal', true)" 
                                            class="flex-1 px-3 py-2 text-xs font-medium text-orange-700 bg-orange-100 rounded-lg hover:bg-orange-200 transition-colors">
                                        Reassign
                                    </button>
                                @else
                                    <button wire:click="openIssueModal({{ $user->id }})" 
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                                        Issue Token
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="px-4 lg:px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $users->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Users Found</h3>
                    <p class="text-gray-500 max-w-sm mx-auto">All eligible users have been processed for this election, or no users match your search criteria.</p>
                </div>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border p-16 text-center">
            <div class="w-20 h-20 mx-auto mb-6 bg-blue-50 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Select an Election</h3>
            <p class="text-gray-600 max-w-md mx-auto">Choose an election from the dropdown above to view eligible users and manage their vote tokens.</p>
        </div>
    @endif
    
    <!-- Issue Token Modal -->
    @if($showIssueModal && $selectedUserId)
        @php $selectedUser = \App\Models\User::find($selectedUserId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data="{ show: @entangle('showIssueModal') }" x-show="show" x-transition>
            <div class="fixed inset-0 bg-black/20" wire:click="$set('showIssueModal', false)"></div>
            <div class="bg-white rounded-lg shadow-xl border max-w-sm w-full relative" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900">Issue Vote Token</h3>
                            <div class="mt-2 space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">User:</span>
                                    <span class="font-medium">{{ $selectedUser->full_name }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Email:</span>
                                    <span class="font-medium truncate ml-2">{{ $selectedUser->email }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Status:</span>
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded text-xs font-medium">{{ $selectedUser->status->label() }}</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-500">Election:</span>
                                    <span class="font-medium truncate ml-2">{{ $elections->find($selectedElection)?->title }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2 mt-4">
                        <button wire:click="$set('showIssueModal', false)" class="flex-1 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button wire:click="accreditSingleUser" class="flex-1 px-3 py-2 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                            Issue Token
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Reassign Modal -->
    @if($showReassignModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showReassignModal') }" x-show="show">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black opacity-50" wire:click="$set('showReassignModal', false)"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Reassign Token</h3>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">New User ID</label>
                        <input wire:model="newUserId" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter user ID">
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="$set('showReassignModal', false)" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancel
                        </button>
                        <button wire:click="reassignToken({{ $reassignTokenId }}, {{ $newUserId }})" wire:click="$set('showReassignModal', false)" class="flex-1 px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                            Reassign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>