<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <div>
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Users</h1>
            <p class="mt-1 text-sm text-gray-500">Manage user accounts and permissions</p>
        </div>
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-3">
            <button wire:click="exportUsers" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
            </button>
            <button wire:click="addUser" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add User
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="bg-white p-3 sm:p-4 rounded-lg border">
            <div class="text-lg sm:text-2xl font-semibold text-gray-900">{{ $totalStats['total'] }}</div>
            <div class="text-xs sm:text-sm text-gray-500">Total</div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg border">
            <div class="text-lg sm:text-2xl font-semibold text-yellow-600">{{ $totalStats['pending'] }}</div>
            <div class="text-xs sm:text-sm text-gray-500">Pending</div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg border">
            <div class="text-lg sm:text-2xl font-semibold text-green-600">{{ $totalStats['approved'] }}</div>
            <div class="text-xs sm:text-sm text-gray-500">Approved</div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg border">
            <div class="text-lg sm:text-2xl font-semibold text-orange-600">{{ $totalStats['suspended'] }}</div>
            <div class="text-xs sm:text-sm text-gray-500">Suspended</div>
        </div>
        <div class="bg-white p-3 sm:p-4 rounded-lg border col-span-2 sm:col-span-1">
            <div class="text-lg sm:text-2xl font-semibold text-red-600">{{ $totalStats['rejected'] }}</div>
            <div class="text-xs sm:text-sm text-gray-500">Rejected</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white p-3 sm:p-4 rounded-lg border">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search users..." class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <select wire:model.live="typeFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="all">All Roles</option>
                <option value="voter">Voters</option>
                <option value="candidate">Candidates</option>
                <option value="admin">Admins</option>
            </select>
            <select wire:model.live="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="all">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="suspended">Suspended</option>
                <option value="rejected">Rejected</option>
            </select>
            <button wire:click="resetFilters" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">
                Reset
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg border overflow-hidden">
        @if($users->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-semibold">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                       ($user->role === 'candidate' ? 'bg-blue-100 text-blue-800' : 
                                       ($user->role === 'observer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full 
                                    {{ $user->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 
                                       ($user->status === 'pending' ? 'bg-amber-100 text-amber-800' : 
                                       ($user->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-rose-100 text-rose-800')) }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($user->created_at)->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="viewUser({{ $user->id }}, '{{ $user->type }}')" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Mobile Cards -->
            <div class="lg:hidden divide-y divide-gray-200">
                @foreach($users as $user)
                <div class="p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $user->email }}</div>
                                <div class="flex items-center space-x-2 mt-2">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                           ($user->role === 'candidate' ? 'bg-blue-100 text-blue-800' : 
                                           ($user->role === 'observer' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $user->status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 
                                           ($user->status === 'pending' ? 'bg-amber-100 text-amber-800' : 
                                           ($user->status === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-rose-100 text-rose-800')) }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button wire:click="viewUser({{ $user->id }}, '{{ $user->type }}')" class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors text-sm">
                            View
                        </button>
                    </div>
                    <div class="text-xs text-gray-500">
                        Joined {{ \Carbon\Carbon::parse($user->created_at)->format('M j, Y') }}
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="p-8 sm:p-12 text-center text-gray-500">
                <svg class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <p class="text-sm">No users found</p>
            </div>
        @endif
    </div>
</div>