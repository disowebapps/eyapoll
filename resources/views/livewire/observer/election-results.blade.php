<div class="space-y-6" wire:poll.30s="refreshResults" wire:loading.class="opacity-50">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Live Election Results</h1>
                    <p class="text-gray-600 mt-1">{{ $election->title }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    @if($isPolling)
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span>Live updates</span>
                    @else
                        <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                        <span>Updates paused</span>
                    @endif
                </div>
                
                <button wire:click="togglePolling" class="px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors text-xs">
                    @if($isPolling)
                        Pause
                    @else
                        Resume
                    @endif
                </button>
                <button wire:click="refreshResults" class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors flex items-center text-xs">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div x-data="{ pressed: false }" 
             @mousedown="pressed = true" 
             @mouseup="pressed = false" 
             @mouseleave="pressed = false"
             @click="$wire.viewVoterRegister()"
             :class="pressed ? 'scale-95' : 'scale-100'"
             class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 text-center cursor-pointer hover:shadow-md hover:border-blue-200 transition-all duration-200 transform">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ number_format($this->overallStats['total_eligible']) }}</h3>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-blue-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 715.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 919.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Accredited Voters</p>
            </div>
        </div>

        <div x-data="{ pressed: false }" 
             @mousedown="pressed = true" 
             @mouseup="pressed = false" 
             @mouseleave="pressed = false"
             :class="pressed ? 'scale-95' : 'scale-100'"
             class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 text-center cursor-pointer hover:shadow-md hover:border-green-200 transition-all duration-200 transform">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ number_format($this->overallStats['total_votes']) }}</h3>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-green-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Total Votes</p>
            </div>
        </div>

        <div x-data="{ pressed: false }" 
             @mousedown="pressed = true" 
             @mouseup="pressed = false" 
             @mouseleave="pressed = false"
             :class="pressed ? 'scale-95' : 'scale-100'"
             class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 text-center cursor-pointer hover:shadow-md hover:border-yellow-200 transition-all duration-200 transform">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ $this->overallStats['turnout_percentage'] }}%</h3>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-yellow-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Voter Turnout</p>
            </div>
        </div>

        <div x-data="{ pressed: false }" 
             @mousedown="pressed = true" 
             @mouseup="pressed = false" 
             @mouseleave="pressed = false"
             :class="pressed ? 'scale-95' : 'scale-100'"
             class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-6 text-center cursor-pointer hover:shadow-md hover:border-purple-200 transition-all duration-200 transform">
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ $this->overallStats['positions_count'] }}</h3>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-purple-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-600">Positions</p>
            </div>
        </div>
    </div>

    <!-- Export and Verification Status -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center space-x-6 mb-4 sm:mb-0">
                <div class="flex items-center space-x-2">
                    <span class="text-sm font-medium text-gray-700">Last Updated:</span>
                    <span class="text-sm text-gray-500">{{ now()->format('M j, Y g:i:s A') }}</span>
                </div>
            </div>
            <div class="flex flex-col items-center sm:items-end space-y-1">
                <div x-data="{ open: false, exporting: false, exportType: '' }" class="relative">
                    <button @click="{{ ($election->isEnded() && $election->results_published) ? 'open = !open' : '' }}" 
                           :disabled="exporting"
                           :class="exporting ? 'bg-gray-400 cursor-not-allowed' : '{{ ($election->isEnded() && $election->results_published) ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed' }}'"
                           class="px-4 py-2 text-white rounded-lg transition-all duration-200 flex items-center text-sm transform"
                           :class="exporting ? 'scale-95' : 'scale-100'">
                        <svg x-show="!exporting" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <svg x-show="exporting" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!exporting">Export Results</span>
                        <span x-show="exporting" x-text="'Exporting ' + exportType + '...'"></span>
                        @if($election->isEnded() && $election->results_published)
                        <svg x-show="!exporting" class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        @endif
                    </button>
                    @if($election->isEnded() && $election->results_published)
                    <div x-show="open && !exporting" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <button @click="exporting = true; exportType = 'CSV'; open = false; $wire.exportResults('csv').then(() => { exporting = false; exportType = ''; })" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export as CSV
                        </button>
                        <button @click="exporting = true; exportType = 'Excel'; open = false; $wire.exportResults('excel').then(() => { exporting = false; exportType = ''; })" 
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export as Excel
                        </button>
                    </div>
                    @endif
                </div>
                @if(!($election->isEnded() && $election->results_published))
                <div class="text-xs text-gray-500 italic">
                    Available after results are published
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Position Results -->
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Position Results</h2>
            <div class="text-sm text-gray-500">{{ count($this->positions) }} position{{ count($this->positions) !== 1 ? 's' : '' }}</div>
        </div>

        @if(count($this->positions) > 0)
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            @foreach($this->positions as $position)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-300">
                <!-- Position Header -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-b border-gray-200">
                    <div class="px-6 py-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $position['title'] }}</h3>
                                @if($position['description'])
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $position['description'] }}</p>
                                @endif
                            </div>
                            <div class="ml-4 text-right flex-shrink-0">
                                <div class="text-2xl font-bold text-indigo-600">{{ number_format($position['total_votes']) }}</div>
                                <div class="text-xs text-gray-500 font-medium">Total Votes</div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ count($position['results']) }} candidate{{ count($position['results']) !== 1 ? 's' : '' }}
                            </span>
                            @if($position['total_votes'] > 0)
                            <div class="text-xs text-gray-500">
                                Last updated {{ now()->diffForHumans() }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Results Content -->
                <div class="p-4 sm:p-6">
                    @if($position['total_votes'] > 0)
                    <div class="space-y-4">
                        <!-- Visual Chart -->
                        <div x-data="{ 
                            results: @js($position['results']), 
                            maxVotes: @js(collect($position['results'])->max('vote_count') ?: 1) 
                        }" class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <div class="space-y-2">
                                <template x-for="result in results" :key="result.candidate_id">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-20 text-xs text-gray-600 truncate" x-text="result.candidate_name.split(' ').slice(-1)[0]"></div>
                                        <div class="flex-1 bg-gray-200 rounded-full h-4 relative overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-1000" 
                                                 :class="result.is_leading ? 'bg-blue-500' : 'bg-gray-400'"
                                                 :style="`width: ${(result.vote_count / maxVotes) * 100}%`"></div>
                                        </div>
                                        <div class="w-12 text-xs font-semibold text-gray-700" x-text="result.vote_count"></div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Candidate Results -->
                        <div class="space-y-3">
                            @foreach($position['results'] as $index => $result)
                            <div class="relative">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl border-l-4 
                                    {{ $result['is_leading'] ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }}
                                    hover:shadow-sm transition-all duration-200">
                                    
                                    <div class="flex items-start space-x-3">
                                        <!-- Ranking Badge -->
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                                {{ $result['ranking'] == 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ $result['ranking'] }}
                                            </div>
                                        </div>
                                        
                                        <!-- Candidate Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1 min-w-0 pr-3">
                                                    <h4 class="text-sm font-semibold text-gray-900 truncate">
                                                        {{ $result['candidate_name'] }}
                                                    </h4>
                                                    @if($result['is_leading'])
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                                        Leading
                                                    </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Vote Count & Percentage -->
                                                <div class="text-right flex-shrink-0">
                                                    <div class="text-lg font-bold text-gray-900">{{ number_format($result['vote_count']) }}</div>
                                                    <div class="text-sm text-gray-500">{{ $result['percentage'] }}%</div>
                                                </div>
                                            </div>
                                            
                                            <!-- Progress Bar -->
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full transition-all duration-500 {{ $result['is_leading'] ? 'bg-blue-500' : 'bg-gray-400' }}"
                                                    style="width: {{ $result['percentage'] }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Abstentions Summary -->
                        @if($position['abstentions'] > 0)
                        <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-amber-800">Abstentions</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-amber-900">{{ number_format($position['abstentions']) }} votes</div>
                                    <div class="text-xs text-amber-700">{{ round(($position['abstentions'] / $position['total_votes']) * 100, 1) }}% of total</div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @else
                    <!-- No Votes State -->
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h4 class="text-sm font-medium text-gray-900 mb-1">No Votes Cast</h4>
                        <p class="text-sm text-gray-500">Results will appear here once voting begins for this position.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- No Positions State -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Positions Available</h3>
            <p class="text-gray-500 max-w-md mx-auto">This election currently has no positions configured for results display.</p>
        </div>
        @endif
    </div>


</div>


