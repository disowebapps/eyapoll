<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Election Results</h1>
                    <p class="text-gray-600 mt-1">{{ $election->title }}</p>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $election->type->label() }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Ended: {{ $election->ended_at ? $election->ended_at->format('M j, Y g:i A') : 'In Progress' }}
                        </span>
                        @if($lastUpdated)
                        <span class="text-sm text-gray-500">
                            Last updated: {{ $lastUpdated->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    @if($isPolling)
                        <button wire:click="togglePolling" class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">
                            Live Updates ON
                        </button>
                    @else
                        <button wire:click="togglePolling" class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full">
                            Live Updates OFF
                        </button>
                    @endif
                    <button wire:click="refreshResults" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(isset($overallStats['message']))
        <!-- No Results Message -->
        <div class="bg-white shadow rounded-lg p-12">
            <div class="text-center">
                <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $overallStats['message'] }}</h3>
                <p class="mt-2 text-gray-500">Election results will be published once voting has ended and results are verified.</p>
            </div>
        </div>
    @else
        <!-- Overall Statistics -->
        <div class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Votes -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Total Votes</h3>
                                <p class="text-2xl font-bold text-blue-600">{{ number_format($overallStats['total_votes']) }}</p>
                                <p class="text-sm text-gray-500">{{ $overallStats['turnout_percentage'] }}% turnout</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Your Total Votes -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Your Total Votes</h3>
                                <p class="text-2xl font-bold text-purple-600">{{ number_format($overallStats['user_total_votes']) }}</p>
                                <p class="text-sm text-gray-500">Across {{ $overallStats['user_positions'] }} position(s)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Positions Won -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Positions Won</h3>
                                <p class="text-2xl font-bold text-yellow-600">{{ $overallStats['user_wins'] }}</p>
                                <p class="text-sm text-gray-500">Out of {{ $overallStats['user_positions'] }} contested</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Eligible Voters -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">Eligible Voters</h3>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($overallStats['total_eligible']) }}</p>
                                <p class="text-sm text-gray-500">{{ $overallStats['positions_count'] }} positions</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Position Results -->
        <div class="space-y-6">
            @foreach($positions as $position)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Position Header -->
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $position['title'] }}</h3>
                            @if($position['description'])
                            <p class="text-sm text-gray-600 mt-1">{{ $position['description'] }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Total Votes</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($position['total_votes']) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Votes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($position['results'] as $result)
                            <tr class="{{ $result['is_current_user'] ? 'bg-blue-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900">#{{ $result['ranking'] }}</span>
                                        @if($result['is_current_user'])
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            You
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $result['candidate_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($result['vote_count']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $result['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-sm text-gray-900">{{ $result['percentage'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($result['is_winning'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Winner
                                        </span>
                                    @elseif($result['is_leading'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Leading
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button
                                        wire:click="viewCandidateDetails({{ $result['candidate_id'] }})"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        View Details
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($position['abstentions'] > 0)
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        <strong>Abstentions:</strong> {{ number_format($position['abstentions']) }} votes
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Export Actions -->
        <div class="mt-6 bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Export Results</h3>
                    <p class="text-sm text-gray-600">Download election results for your records</p>
                </div>
                <button
                    wire:click="exportResults"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                >
                    Export PDF
                </button>
            </div>
        </div>
    @endif
</div>