<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('observer.elections') }}" 
                   class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Elections
                </a>
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    {{ $election->status->value === 'active' ? 'bg-green-100 text-green-800 border border-green-200' : '' }}
                    {{ $election->status->value === 'scheduled' ? 'bg-blue-100 text-blue-800 border border-blue-200' : '' }}
                    {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800 border border-gray-200' : '' }}
                    {{ $election->status->value === 'cancelled' ? 'bg-red-100 text-red-800 border border-red-200' : '' }}">
                    <div class="w-2 h-2 rounded-full mr-2
                        {{ $election->status->value === 'active' ? 'bg-green-500' : '' }}
                        {{ $election->status->value === 'scheduled' ? 'bg-blue-500' : '' }}
                        {{ $election->status->value === 'ended' ? 'bg-gray-500' : '' }}
                        {{ $election->status->value === 'cancelled' ? 'bg-red-500' : '' }}"></div>
                    {{ $election->status->label() }}
                </span>
            </div>
        </div>
    </div>
        <!-- Election Title & Overview -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-blue-600 px-6 py-8 sm:px-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2 text-center sm:text-left">{{ $election->title }}</h1>
                    <p class="text-blue-100 text-sm sm:text-base max-w-3xl text-center sm:text-left">
                        {{ $election->description ?? 'Comprehensive election overview with positions and candidate information' }}
                    </p>
                </div>
                
                <!-- Election Statistics -->
                <div class="px-6 py-6 sm:px-8">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $election->positions->count() }}</div>
                            <div class="text-sm text-gray-500 font-medium">Total Positions</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $election->positions->sum(fn($p) => $p->candidates->count()) }}</div>
                            <div class="text-sm text-gray-500 font-medium">Total Candidates</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $election->starts_at->format('M j') }}</div>
                            <div class="text-sm text-gray-500 font-medium">Start Date</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $election->ends_at->format('M j') }}</div>
                            <div class="text-sm text-gray-500 font-medium">End Date</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Positions Grid -->
        <div class="space-y-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Positions</h2>
                <div class="text-sm text-gray-500">
                    {{ $election->positions->count() }} position{{ $election->positions->count() !== 1 ? 's' : '' }} available
                </div>
            </div>

            @if($election->positions->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($election->positions as $position)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <!-- Position Header -->
                    <div class="bg-gray-50 border-b border-gray-200">
                        <div class="px-6 py-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <button 
                                        wire:click="viewPositionDetails({{ $position->id }})"
                                        class="text-left group"
                                    >
                                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $position->title }}
                                        </h3>
                                        @if($position->description)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                            {{ Str::limit($position->description, 120) }}
                                        </p>
                                        @endif
                                    </button>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        {{ $position->candidates->count() }} candidate{{ $position->candidates->count() !== 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Candidates List -->
                    <div class="p-6">
                        @if($position->candidates->count() > 0)
                        <div class="space-y-3">
                            @foreach($position->candidates as $candidate)
                            <button 
                                wire:click="viewCandidateProfile({{ $candidate->id }})"
                                class="w-full text-left p-4 border border-gray-200 rounded-lg hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            >
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-full flex items-center justify-center shadow-sm">
                                            <span class="text-white font-semibold text-sm">
                                                {{ substr($candidate->user->first_name, 0, 1) }}{{ substr($candidate->user->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors">
                                                    {{ $candidate->user->full_name }}
                                                </p>
                                                @if($candidate->party_affiliation)
                                                <p class="text-xs text-gray-500 mt-0.5">
                                                    {{ $candidate->party_affiliation }}
                                                </p>
                                                @endif
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">No Candidates Registered</h4>
                            <p class="text-sm text-gray-500">Candidates for this position have not been registered yet.</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Positions Available</h3>
                <p class="text-gray-500 max-w-md mx-auto">This election currently has no positions configured. Positions will appear here once they are added by election administrators.</p>
            </div>
            @endif
</div>