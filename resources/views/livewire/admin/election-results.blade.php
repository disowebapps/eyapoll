<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto p-4">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-left">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Election Results</h1>
                    <p class="text-gray-600 mt-1 md:mt-2 text-sm md:text-base">{{ $this->election->title }}</p>
                </div>
                <button wire:click="refreshResults" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm md:text-base">
                    Refresh
                </button>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6 mb-6 md:mb-8">
            <a href="{{ route('admin.elections.eligible-voters', $this->electionId) }}" class="bg-white rounded-lg p-4 md:p-6 text-center hover:bg-gray-50 transition-colors cursor-pointer">
                <div class="text-xl md:text-3xl font-bold text-gray-900">{{ number_format($this->overallStats['total_eligible'] ?? 0) }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Eligible Voters</div>
            </a>
            <div class="bg-white rounded-lg p-4 md:p-6 text-center">
                <div class="text-xl md:text-3xl font-bold text-green-600">{{ number_format($this->overallStats['total_votes'] ?? 0) }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Total Votes</div>
            </div>
            <div class="bg-white rounded-lg p-4 md:p-6 text-center">
                <div class="text-xl md:text-3xl font-bold text-blue-600">{{ $this->overallStats['turnout_percentage'] ?? 0 }}%</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Turnout</div>
            </div>
            <div class="bg-white rounded-lg p-4 md:p-6 text-center">
                <div class="text-xl md:text-3xl font-bold text-purple-600">{{ $this->overallStats['positions_count'] ?? 0 }}</div>
                <div class="text-xs md:text-sm text-gray-600 mt-1">Positions</div>
            </div>
        </div>

        <!-- Results -->
        <div class="space-y-6 md:space-y-8">
            @foreach($this->positions as $position)
            <div class="bg-white rounded-lg p-4 md:p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4 md:mb-6">
                    <div class="text-center sm:text-left">
                        <h3 class="text-lg md:text-xl font-semibold text-gray-900">{{ $position['title'] ?? 'Unknown Position' }}</h3>
                    </div>
                    <div class="text-center sm:text-right text-xs md:text-sm text-gray-500">
                        <div>{{ number_format($position['total_votes'] ?? 0) }} votes</div>
                        <div>{{ count($position['results'] ?? []) }} candidates</div>
                    </div>
                </div>

                @if(($position['total_votes'] ?? 0) > 0)
                    <div class="space-y-3 md:space-y-4">
                        @foreach($position['results'] ?? [] as $result)
                        <div class="border rounded-lg p-3 md:p-4 {{ ($result['is_winning'] ?? false) ? 'bg-green-50 border-green-200' : 'bg-gray-50' }}">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                                <div class="font-medium text-gray-900 text-sm md:text-base text-center sm:text-left">{{ $result['candidate_name'] ?? 'Unknown Candidate' }}</div>
                                <div class="flex items-center justify-center sm:justify-end space-x-3">
                                    <span class="text-xs md:text-sm font-medium">{{ number_format($result['vote_count'] ?? 0) }} votes</span>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-xs text-gray-500">Rank:</span>
                                        <span class="w-5 h-5 md:w-6 md:h-6 rounded-full text-xs font-medium flex items-center justify-center {{ ($result['ranking'] ?? 0) == 1 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $result['ranking'] ?? 0 }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 md:h-3 mb-2">
                                <div class="h-2 md:h-3 rounded-full {{ ($result['is_winning'] ?? false) ? 'bg-green-600' : 'bg-blue-600' }} transition-all duration-300" 
                                     x-data="{ width: {{ $result['percentage'] ?? 0 }} }" 
                                     :style="`width: ${width}%`"></div>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs md:text-sm font-medium text-gray-700">{{ $result['percentage'] ?? 0 }}%</span>
                                @if($result['is_winning'] ?? false)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Winner
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 md:py-12 text-gray-500">
                        <div class="text-base md:text-lg font-medium">No votes cast yet</div>
                        <div class="text-xs md:text-sm mt-1">Results will appear once voting begins</div>
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Verification Status -->
        <div class="bg-white rounded-lg p-4 md:p-6 mt-6 md:mt-8">
            <h3 class="text-base md:text-lg font-medium text-gray-900 mb-3 md:mb-4 text-center sm:text-left">Verification Status</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                <div class="flex items-center justify-between p-3 border rounded">
                    <span class="font-medium text-gray-700 text-sm md:text-base">Chain Integrity</span>
                    @if($this->verificationStatus['chain_verified'] ?? false)
                        <span class="text-green-600 font-medium text-sm md:text-base">✓ Verified</span>
                    @else
                        <span class="text-red-600 font-medium text-sm md:text-base">✗ Failed</span>
                    @endif
                </div>
                <div class="flex items-center justify-between p-3 border rounded">
                    <span class="font-medium text-gray-700 text-sm md:text-base">Tally Verification</span>
                    <span class="text-green-600 font-medium text-sm md:text-base">
                        {{ $this->verificationStatus['tallies_verified']['percentage'] ?? 0 }}%
                    </span>
                </div>
            </div>
            <p class="text-xs md:text-sm text-gray-500 mt-3 text-center sm:text-left">Last updated: {{ ($this->verificationStatus['last_verification'] ?? now())->format('M j, g:i A') }}</p>
        </div>
    </div>
</div>