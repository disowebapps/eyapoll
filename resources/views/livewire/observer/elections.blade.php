<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Elections Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Monitor and observe all elections</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="hidden sm:flex items-center space-x-4 text-sm">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-gray-600">Active Elections</span>
                        </div>
                        <div class="text-gray-400">|</div>
                        <span class="text-gray-600">{{ $this->elections->total() }} Total</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
                <!-- Search -->
                <div class="relative flex-1">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Search elections by title..." 
                           class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                
                <!-- Status Filter -->
                <div class="flex items-center bg-gray-50 rounded-lg px-4 py-3 border border-gray-200 min-w-0 sm:min-w-[160px]">
                    <svg class="w-4 h-4 text-gray-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                    </svg>
                    <select wire:model.live="statusFilter" class="text-sm bg-transparent border-0 focus:ring-0 text-gray-700 font-medium w-full">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="ended">Ended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Desktop Table -->
        <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Election Details</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Positions</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Votes Cast</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Turnout</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->elections as $election)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-start">
                                    @if($election->status->value === 'active')
                                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3 flex-shrink-0 animate-pulse"></div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-gray-900 text-sm leading-5">{{ $election->title }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span class="inline-flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $election->starts_at->format('M j, Y') }} - {{ $election->ends_at->format('M j, Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $election->status->value === 'active' ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' : '' }}
                                    {{ $election->status->value === 'scheduled' ? 'bg-blue-100 text-blue-800 ring-1 ring-blue-600/20' : '' }}
                                    {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20' : '' }}
                                    {{ $election->status->value === 'cancelled' ? 'bg-red-100 text-red-800 ring-1 ring-red-600/20' : '' }}">
                                    @if($election->status->value === 'active')
                                        <svg class="w-2 h-2 mr-1 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                    @endif
                                    {{ $election->status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-semibold text-gray-900">{{ $election->positions_count ?? 0 }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($election->votes_count ?? 0) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="inline-flex items-center">
                                    <div class="text-sm font-semibold text-gray-900">{{ $election->voter_turnout }}%</div>
                                    @if($election->voter_turnout > 0)
                                        <div class="ml-2 w-12 bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ min($election->voter_turnout, 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <button wire:click="viewElectionDetails({{ $election->id }})" 
                                            class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Details
                                    </button>
                                    @if($election->status->value === 'active' || $election->status->value === 'ended')
                                        <button wire:click="viewElectionResults({{ $election->id }})" 
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                            Results
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                    <h3 class="text-sm font-medium text-gray-900 mb-1">No elections found</h3>
                                    <p class="text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-4">
            @forelse($this->elections as $election)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <!-- Card Header -->
                <div class="p-4 pb-3">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start">
                                @if($election->status->value === 'active')
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-2 flex-shrink-0 animate-pulse"></div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 leading-5">{{ $election->title }}</h3>
                                    <div class="flex items-center mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $election->starts_at->format('M j, Y') }} - {{ $election->ends_at->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="ml-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium flex-shrink-0
                            {{ $election->status->value === 'active' ? 'bg-green-100 text-green-800 ring-1 ring-green-600/20' : '' }}
                            {{ $election->status->value === 'scheduled' ? 'bg-blue-100 text-blue-800 ring-1 ring-blue-600/20' : '' }}
                            {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20' : '' }}
                            {{ $election->status->value === 'cancelled' ? 'bg-red-100 text-red-800 ring-1 ring-red-600/20' : '' }}">
                            @if($election->status->value === 'active')
                                <svg class="w-2 h-2 mr-1 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                            @endif
                            {{ $election->status->label() }}
                        </span>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="px-4 pb-4">
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <div class="text-lg font-bold text-gray-900">{{ $election->positions_count ?? 0 }}</div>
                            <div class="text-xs text-gray-600 font-medium">Positions</div>
                        </div>
                        <div class="text-center p-3 bg-indigo-50 rounded-lg">
                            <div class="text-lg font-bold text-indigo-900">{{ number_format($election->votes_count ?? 0) }}</div>
                            <div class="text-xs text-indigo-700 font-medium">Votes Cast</div>
                        </div>
                        <div class="text-center p-3 bg-green-50 rounded-lg">
                            <div class="text-lg font-bold text-green-900">{{ $election->voter_turnout }}%</div>
                            <div class="text-xs text-green-700 font-medium">Turnout</div>
                            @if($election->voter_turnout > 0)
                                <div class="mt-1 w-full bg-green-200 rounded-full h-1">
                                    <div class="bg-green-600 h-1 rounded-full" style="width: {{ min($election->voter_turnout, 100) }}%"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="px-4 pb-4">
                    <div class="flex space-x-3">
                        <button wire:click="viewElectionDetails({{ $election->id }})" 
                                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Details
                        </button>
                        @if($election->status->value === 'active' || $election->status->value === 'ended')
                            <button wire:click="viewElectionResults({{ $election->id }})" 
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Results
                            </button>
                        @else
                            <div class="flex-1 flex items-center justify-center px-4 py-2.5 text-sm text-gray-400 bg-gray-50 rounded-lg">
                                No results yet
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="text-center">
                    <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No elections found</h3>
                    <p class="text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                </div>
            </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($this->elections->hasPages())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
            <div class="px-4 sm:px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-500 text-center sm:text-left">
                        Showing <span class="font-medium text-gray-900">{{ $this->elections->firstItem() }}</span> to 
                        <span class="font-medium text-gray-900">{{ $this->elections->lastItem() }}</span> of 
                        <span class="font-medium text-gray-900">{{ $this->elections->total() }}</span> elections
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        @if($this->elections->onFirstPage())
                            <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm font-medium">Previous</span>
                        @else
                            <button wire:click="previousPage" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors text-sm font-medium">Previous</button>
                        @endif
                        
                        <div class="hidden sm:flex items-center space-x-1">
                            @foreach($this->elections->getUrlRange(max(1, $this->elections->currentPage() - 2), min($this->elections->lastPage(), $this->elections->currentPage() + 2)) as $page => $url)
                                @if($page == $this->elections->currentPage())
                                    <span class="px-4 py-2 text-white bg-indigo-600 rounded-lg font-medium text-sm shadow-sm">{{ $page }}</span>
                                @else
                                    <button wire:click="gotoPage({{ $page }})" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors text-sm font-medium">{{ $page }}</button>
                                @endif
                            @endforeach
                        </div>
                        
                        <div class="sm:hidden px-4 py-2 text-sm text-gray-700 font-medium">
                            Page {{ $this->elections->currentPage() }} of {{ $this->elections->lastPage() }}
                        </div>
                        
                        @if($this->elections->hasMorePages())
                            <button wire:click="nextPage" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors text-sm font-medium">Next</button>
                        @else
                            <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm font-medium">Next</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>