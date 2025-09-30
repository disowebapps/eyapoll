<div>
    <!-- Welcome Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-3xl font-bold">Welcome back, {{ Auth::guard('candidate')->user()->first_name }}!</h2>
            <p class="mt-2 text-purple-100">Manage your candidacy applications and track your election progress.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Applications -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Total Applications</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ $statistics['total_applications'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approved Applications -->
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
                            <h3 class="text-lg font-medium text-gray-900">Approved</h3>
                            <p class="text-2xl font-bold text-green-600">{{ $statistics['approved_applications'] }}</p>
                            <p class="text-sm text-gray-500">{{ $statistics['approval_rate'] }}% approval rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Votes -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Total Votes</h3>
                            <p class="text-2xl font-bold text-purple-600">{{ number_format($statistics['total_votes']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Elections Won -->
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
                            <h3 class="text-lg font-medium text-gray-900">Elections Won</h3>
                            <p class="text-2xl font-bold text-yellow-600">{{ $statistics['won_elections'] }}</p>
                            <p class="text-sm text-gray-500">{{ $statistics['win_rate'] }}% win rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Applications -->
    @if($hasActiveApplications)
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Active Applications</h3>
                <p class="text-sm text-gray-600 mt-1">Applications currently being processed or approved</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($activeApplications as $application)
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-{{ $application['status_color'] }}-100 rounded-lg flex items-center justify-center">
                                    @if($application['status'] === 'approved')
                                    <svg class="w-5 h-5 text-{{ $application['status_color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5 text-{{ $application['status_color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $application['election_title'] }}</h4>
                                    <p class="text-xs text-gray-500">{{ $application['position_title'] }} • {{ $application['election_type'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $application['created_at']->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $application['status_color'] }}-100 text-{{ $application['status_color'] }}-800">
                                {{ $application['status_label'] }}
                            </span>

                            @if($application['has_vote_results'])
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($application['vote_count']) }} votes</div>
                                <div class="text-xs text-gray-500">{{ $application['vote_percentage'] }}% • Rank #{{ $application['ranking'] }}</div>
                                @if($application['is_winner'])
                                <div class="text-xs text-green-600 font-medium">🏆 Winner</div>
                                @endif
                            </div>
                            @endif

                            <div class="flex space-x-2">
                                <button
                                    wire:click="viewApplication({{ $application['id'] }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View
                                </button>

                                @if($application['can_withdraw'])
                                <button
                                    wire:click="withdrawApplication({{ $application['id'] }})"
                                    onclick="return confirm('Are you sure you want to withdraw this application?')"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Withdraw
                                </button>
                                @endif

                                @if($application['has_vote_results'])
                                <button
                                    wire:click="viewElectionResults({{ $application['id'] }})"
                                    class="text-purple-600 hover:text-purple-800 text-sm font-medium"
                                >
                                    Results
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Applications -->
    @if($hasPendingApplications)
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Pending Review</h3>
                <p class="text-sm text-gray-600 mt-1">Applications awaiting administrator approval</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($pendingApplications as $application)
                    <div class="flex items-center justify-between p-4 border border-yellow-200 bg-yellow-50 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $application['election_title'] }}</h4>
                                    <p class="text-xs text-gray-500">{{ $application['position_title'] }} • {{ $application['election_type'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $application['created_at']->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <div class="text-sm text-gray-600">{{ $application['documents_count'] }} documents</div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                                    <div class="bg-yellow-600 h-2 rounded-full" style="width: {{ $application['progress']['percentage'] }}%"></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">{{ $application['progress']['percentage'] }}% complete</div>
                            </div>

                            <div class="flex space-x-2">
                                <button
                                    wire:click="viewApplication({{ $application['id'] }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View
                                </button>

                                @if($application['can_withdraw'])
                                <button
                                    wire:click="withdrawApplication({{ $application['id'] }})"
                                    onclick="return confirm('Are you sure you want to withdraw this application?')"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Withdraw
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Application History -->
    @if($hasRejectedApplications || (count($applications) > count($activeApplications)))
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Application History</h3>
                <p class="text-sm text-gray-600 mt-1">Past applications and their outcomes</p>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($applications as $application)
                    @if(!in_array($application['status'], ['pending', 'approved']))
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-{{ $application['status_color'] }}-100 rounded-lg flex items-center justify-center">
                                    @if($application['status'] === 'rejected')
                                    <svg class="w-5 h-5 text-{{ $application['status_color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    @elseif($application['status'] === 'withdrawn')
                                    <svg class="w-5 h-5 text-{{ $application['status_color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    @else
                                    <svg class="w-5 h-5 text-{{ $application['status_color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    @endif
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ $application['election_title'] }}</h4>
                                    <p class="text-xs text-gray-500">{{ $application['position_title'] }} • {{ $application['election_type'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $application['created_at']->format('M j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $application['status_color'] }}-100 text-{{ $application['status_color'] }}-800">
                                {{ $application['status_label'] }}
                            </span>

                            @if($application['has_vote_results'] && $application['status'] === 'approved')
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">{{ number_format($application['vote_count']) }} votes</div>
                                <div class="text-xs text-gray-500">{{ $application['vote_percentage'] }}%</div>
                                @if($application['is_winner'])
                                <div class="text-xs text-green-600 font-medium">🏆 Winner</div>
                                @endif
                            </div>
                            @endif

                            <button
                                wire:click="viewApplication({{ $application['id'] }})"
                                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                            >
                                View Details
                            </button>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a
                        href="{{ route('candidate.apply', ['election' => 'latest']) }}"
                        class="flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Apply for Election
                    </a>

                    <button
                        wire:click="refreshData"
                        class="flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh Data
                    </button>

                    <a
                        href="{{ route('voter.dashboard') }}"
                        class="flex items-center justify-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Switch to Voter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    @if(!$hasActiveApplications && !$hasPendingApplications && !$hasRejectedApplications && count($applications) === 0)
    <div class="text-center py-12">
        <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No Applications Yet</h3>
        <p class="mt-2 text-gray-500">You haven't applied for any elections yet. Start your political journey today!</p>
        <div class="mt-6">
            <a
                href="{{ route('candidate.apply', ['election' => 'latest']) }}"
                class="btn-primary inline-flex items-center"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Apply for Your First Election
            </a>
        </div>
    </div>
    @endif
</div>