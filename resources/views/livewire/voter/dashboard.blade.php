<div>
    @if(in_array($this->kycStatus['status'], ['required', 'rejected']))
    <div class="mb-6">
        <div class="bg-{{ $this->kycStatus['color'] === 'red' ? 'red' : 'yellow' }}-500 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center">
                <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-xl font-bold">{{ $this->kycStatus['text'] }}</h3>
                    <p class="mt-1">{{ $this->kycStatus['subtext'] }}</p>
                </div>
                <a href="{{ route('voter.kyc') }}" class="bg-white text-{{ $this->kycStatus['color'] === 'red' ? 'red' : 'yellow' }}-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition">
                    {{ $this->kycStatus['status'] === 'rejected' ? 'Upload New' : 'Upload KYC' }}
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="space-y-6">
        <div class="bg-blue-600 rounded-lg shadow-lg p-6 text-white">
            <h2 class="text-3xl font-bold">Hi, {{ Auth::user()->first_name }}!</h2>
            <p class="mt-2 text-blue-100">
                @if(in_array($this->kycStatus['status'], ['required', 'rejected']))
                    Complete your verification to participate in democracy.
                @elseif($this->kycStatus['status'] === 'pending')
                    Your documents are under review. You'll be notified once approved.
                @else
                    Ready to participate? Check out available elections below.
                @endif
            </p>
        </div>


        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white overflow-hidden shadow rounded-lg {{ $this->electionStats['status'] !== 'none' ? 'cursor-pointer hover:shadow-md transition-shadow' : '' }}"
                  @if($this->electionStats['status'] !== 'none') @click="activeTab = 'elections'" @endif>
                <div class="p-4 text-center">
                    @php
                        $iconColors = [
                            'none' => 'bg-gray-500',
                            'ready' => 'bg-blue-500',
                            'partial' => 'bg-orange-500',
                            'completed' => 'bg-green-500'
                        ];
                        $textColors = [
                            'none' => 'text-gray-600',
                            'ready' => 'text-blue-600',
                            'partial' => 'text-orange-600',
                            'completed' => 'text-green-600'
                        ];
                    @endphp
                    <div class="w-10 h-10 {{ $iconColors[$this->electionStats['status']] }} rounded-md flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Active Elections</h3>
                    <p class="text-2xl font-bold {{ $textColors[$this->electionStats['status']] }} mb-1">{{ $this->dashboardData['active_count'] }}</p>
                    <p class="text-xs text-gray-500">{!! $this->electionStats['text'] !!}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg cursor-pointer hover:shadow-md transition-shadow"
                  x-data="{ scrollToHistory() { activeTab = 'voting-history'; } }"
                  @click="scrollToHistory()">
                <div class="p-4 text-center">
                    <div class="w-10 h-10 bg-green-500 rounded-md flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Vote Cast</h3>
                    <p class="text-2xl font-bold text-green-600 mb-1">{{ $this->dashboardData['votes_cast'] }}</p>
                    <p class="text-xs text-gray-500">Total votes</p>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg">
                <div class="p-4 text-center min-h-[140px] flex flex-col justify-center">
                    @php
                        $statusColors = [
                            'accredited' => ['icon' => 'bg-green-500', 'text' => 'text-green-600'],
                            'verified' => ['icon' => 'bg-blue-500', 'text' => 'text-blue-600'],
                            'pending' => ['icon' => 'bg-red-500', 'text' => 'text-red-600']
                        ];
                        $colors = $statusColors[$this->accountStatus['status']];
                    @endphp
                    <div class="w-10 h-10 {{ $colors['icon'] }} rounded-md flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Account Status</h3>
                    <p class="text-lg font-bold {{ $colors['text'] }} mb-1">{{ $this->accountStatus['text'] }}</p>
                    <p class="text-xs text-gray-500 leading-tight break-words">{{ $this->accountStatus['subtext'] }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-4 text-center">
                    <div class="w-10 h-10 bg-purple-500 rounded-md flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Participation</h3>
                    <p class="text-2xl font-bold text-purple-600 mb-1">{{ $this->dashboardData['participation_rate'] }}%</p>
                    <p class="text-xs text-gray-500">Engagement rate</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-900">Recent Elections</h3>
                    <a href="{{ route('voter.elections') }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">View All</a>
                </div>
                <hr class="border-blue-200 w-full mb-6">
                @if($this->dashboardData['elections']->count() > 0)
                <div class="space-y-4">
                    @foreach($this->dashboardData['elections'] as $election)
                    @php
                        $timeService = app(\App\Services\Election\ElectionTimeService::class);
                        try {
                            $currentStatus = $timeService->getElectionStatus($election);
                            $statusLabel = $currentStatus->label();
                            $statusBadgeClass = $currentStatus->getBadgeClass();
                            $dateLabel = match($currentStatus) {
                                \App\Enums\Election\ElectionStatus::UPCOMING => 'Starts: ' . $election->starts_at->format('M j, Y g:i A'),
                                \App\Enums\Election\ElectionStatus::ONGOING => 'Ends: ' . $election->ends_at->format('M j, Y g:i A'),
                                \App\Enums\Election\ElectionStatus::COMPLETED => 'Ended: ' . $election->ends_at->format('M j, Y g:i A'),
                                default => 'Date: ' . $election->ends_at->format('M j, Y g:i A')
                            };
                        } catch (\Exception $e) {
                            $statusLabel = 'Unknown';
                            $statusBadgeClass = 'bg-gray-100 text-gray-800';
                            $dateLabel = 'Date: ' . ($election->ends_at ? $election->ends_at->format('M j, Y g:i A') : 'N/A');
                        }
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-4 transition-all duration-200" 
                         x-data="{ hovered: false, showModal: false, showApplicationModal: false }" 
                         @mouseenter="hovered = true" 
                         @mouseleave="hovered = false"
                         :class="{ 'shadow-lg border-blue-300 bg-blue-50': hovered, 'shadow-sm': !hovered }">
                        <h4 class="font-semibold text-gray-900 mb-1 transition-colors" :class="{ 'text-blue-900': hovered }">{{ $election->title }}</h4>
                        <p class="text-sm text-gray-600 inline transition-colors" :class="{ 'text-blue-700': hovered }">{{ $election->description }} </p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeClass }} transition-transform" :class="{ 'scale-105': hovered }">
                            {{ $statusLabel }}
                        </span>

                        <div class="mt-2">
                            <span class="text-xs text-gray-500 transition-colors" :class="{ 'text-blue-600': hovered }">
                                {{ $dateLabel }}
                            </span>
                        </div>
                        @if($currentStatus === \App\Enums\Election\ElectionStatus::ONGOING)
                            <div class="mt-3 flex gap-2">
                                @if($this->accountStatus['status'] === 'accredited')
                                    <a href="{{ route('voter.vote', $election->id) }}" 
                                       class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Vote Now
                                    </a>
                                @else
                                    <button @click="showModal = true" 
                                            class="inline-flex items-center bg-gray-400 text-white px-4 py-2 rounded text-sm font-medium cursor-not-allowed hover:bg-gray-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        Not Accredited
                                    </button>
                                @endif
                            </div>
                        @elseif($currentStatus === \App\Enums\Election\ElectionStatus::UPCOMING)
                            <div class="mt-3">
                                @can('apply', $election)
                                    <a href="{{ route('candidate.apply', $election->id) }}" 
                                       class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-700 transform hover:scale-105 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Apply as Candidate
                                    </a>
                                @else
                                    <button @click="showApplicationModal = true" 
                                            class="inline-flex items-center bg-gray-400 text-white px-4 py-2 rounded text-sm font-medium cursor-not-allowed hover:bg-gray-500 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Apply as Candidate
                                    </button>
                                @endcan
                            </div>
                        @endif
                        @if($currentStatus === \App\Enums\Election\ElectionStatus::ONGOING && $this->accountStatus['status'] !== 'accredited')
                            <div x-show="showModal" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-90"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-90"
                                 x-cloak 
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                                <div class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-2xl">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900">Not Eligible to Vote</h3>
                                    </div>
                                    <p class="text-gray-600 mb-6">Accreditation for this election has closed. Only voters who were accredited before the election started can participate.</p>
                                    <div class="flex justify-center">
                                        <button @click="showModal = false" 
                                                class="bg-gray-200 text-gray-800 px-6 py-2 rounded text-sm font-medium hover:bg-gray-300 transition-colors">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @cannot('apply', $election)
                            @if($currentStatus === \App\Enums\Election\ElectionStatus::UPCOMING)
                                <div x-show="showApplicationModal" 
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 scale-90"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-90"
                                     x-cloak 
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                                    <div class="bg-white rounded-lg p-6 max-w-sm mx-4 shadow-2xl">
                                        <div class="flex items-center mb-4">
                                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-semibold text-gray-900">Application Not Available</h3>
                                        </div>
                                        <p class="text-gray-600 mb-6">
                                            @if($election->candidate_register_ends && now()->gt($election->candidate_register_ends))
                                                Candidate application for {{ $election->title }} ended on {{ $election->candidate_register_ends->format('M j, Y g:i A') }}
                                            @elseif($election->candidate_register_starts && now()->lt($election->candidate_register_starts))
                                                Applications open on {{ $election->candidate_register_starts->format('M j, Y g:i A') }}
                                            @else
                                                Applications are currently open
                                            @endif
                                        </p>
                                        <div class="flex justify-center">
                                            <button @click="showApplicationModal = false" 
                                                    class="bg-gray-200 text-gray-800 px-6 py-2 rounded text-sm font-medium hover:bg-gray-300 transition-colors">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endcannot
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Elections Available</h4>
                    <p class="text-gray-500">There are currently no active elections. Check back later for upcoming elections.</p>
                </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 pb-84 lg:pb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Recent Vote</h3>
                <a href="{{ route('voter.history') }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">View All</a>
            </div>
            <hr class="border-blue-200 w-full mb-6">
            @if(count($this->recentVoteHistory) > 0)
            <div class="space-y-4">
                @foreach($this->recentVoteHistory as $vote)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer touch-manipulation"
                      onclick="window.location.href='{{ route('voter.history') }}'">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                        <div class="flex-1 mb-2 sm:mb-0">
                            <h4 class="font-semibold text-gray-900 mb-1">{{ $vote['election_title'] }}</h4>
                            <p class="text-sm text-gray-600">Voted for: {{ $vote['candidates'] }}</p>
                        </div>
                        <div class="flex flex-col sm:items-end sm:text-right">
                            <span class="text-xs text-gray-500 mb-1">
                                Voted: {{ $vote['cast_at']->format('M j, Y g:i A') }}
                            </span>
                            <a href="{{ route('voter.receipt', ['election' => $vote['receipt_hash']]) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                View Receipt
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h4 class="text-lg font-medium text-gray-900 mb-2">No Vote History</h4>
                <p class="text-gray-500">Your completed votes will appear here once you participate in elections.</p>
            </div>
            @endif
            </div>
        </div>
    </div>
</div>
