<div class="max-w-4xl mx-auto space-y-6 pb-20 lg:pb-0">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Elections</h1>
        <p class="text-gray-600">Choose an election to cast your vote</p>
    <!-- Filter Tabs -->
    <div class="flex flex-wrap justify-center gap-2 mb-6">
        <button wire:click="$set('statusFilter', 'all')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            All
        </button>
        <button wire:click="$set('statusFilter', 'upcoming')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'upcoming' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Upcoming
        </button>
        <button wire:click="$set('statusFilter', 'ongoing')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'ongoing' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Ongoing
        </button>
        <button wire:click="$set('statusFilter', 'completed')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'completed' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Completed
        </button>
        <button wire:click="$set('statusFilter', 'cancelled')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Cancelled
        </button>
        <button wire:click="$set('statusFilter', 'archived')"
                class="px-4 py-2 rounded-lg font-medium transition-colors {{ $statusFilter === 'archived' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Archived
        </button>
    </div>
    </div>

    @if($this->votingData['elections']->count() > 0)
        <!-- Elections Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($this->votingData['elections'] as $election)
                @php
                    $hasVoted = in_array($election->id, $this->votingData['voted_ids']);
                @endphp
            <div class="border border-gray-200 rounded-lg p-4 transition-all duration-200" 
                 x-data="{ hovered: false, showModal: false }" 
                 @mouseenter="hovered = true" 
                 @mouseleave="hovered = false"
                 :class="{ 'shadow-lg border-blue-300 bg-blue-50': hovered, 'shadow-sm': !hovered }">
                @php
                    $timeService = app(\App\Services\Election\ElectionTimeService::class);
                    try {
                        $currentStatus = $timeService->getElectionStatus($election);
                        $statusLabel = $currentStatus->label();
                        $statusBadgeClass = $currentStatus->getBadgeClass();
                        $dateLabel = match($currentStatus) {
                            \App\Enums\Election\ElectionStatus::UPCOMING => 'Election starts: ' . $election->starts_at->format('M j, Y g:i A'),
                            \App\Enums\Election\ElectionStatus::ONGOING => 'Voting ends: ' . $election->ends_at->format('M j, Y g:i A'),
                            \App\Enums\Election\ElectionStatus::COMPLETED => 'Voting ended: ' . $election->ends_at->format('M j, Y g:i A'),
                            default => 'Election date: ' . $election->ends_at->format('M j, Y g:i A')
                        };
                    } catch (\Exception $e) {
                        $statusLabel = 'Unknown';
                        $statusBadgeClass = 'bg-gray-100 text-gray-800';
                        $dateLabel = 'Date: ' . ($election->ends_at ? $election->ends_at->format('M j, Y g:i A') : 'N/A');
                    }
                @endphp
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
                    <div class="mt-3">
                        @php
                            $user = auth()->user();
                            $isAccredited = $user->status->value === 'active';
                        @endphp
                        @if($isAccredited)
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
                                        <h3 class="text-lg font-semibold text-gray-900">Account Not Accredited</h3>
                                    </div>
                                    <p class="text-gray-600 mb-6">You must be accredited to vote. Please complete your verification process to participate in elections.</p>
                                    <div class="flex space-x-3">
                                        <button @click="showModal = false" 
                                                class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded text-sm font-medium hover:bg-gray-300 transition-colors">
                                            Close
                                        </button>
                                        <a href="{{ route('voter.kyc') }}" 
                                           class="flex-1 bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-blue-700 transition-colors text-center">
                                            Verify Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @elseif($currentStatus === \App\Enums\Election\ElectionStatus::UPCOMING)
                    <div class="mt-3">
                        @if($election->user_has_applied)
                            <a href="{{ route('candidate.apply', $election->id) }}" class="inline-flex items-center bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">
                                View Application
                            </a>
                        @elseif($election->application_ended)
                            <button class="inline-flex items-center bg-red-500 text-white px-4 py-2 rounded text-sm font-medium cursor-not-allowed">
                                Application ended
                            </button>
                        @elseif($election->user_can_apply)
                            <a href="{{ route('candidate.apply', $election->id) }}" class="inline-flex items-center bg-green-600 text-white px-4 py-2 rounded text-sm font-medium">
                                Apply as Candidate
                            </a>
                        @else
                            <button class="inline-flex items-center bg-gray-400 text-white px-4 py-2 rounded text-sm font-medium cursor-not-allowed">
                                Cannot apply
                            </button>
                        @endif
                    </div>
                @endif
            </div>
            @endforeach
        </div>

        @if($this->votingData['has_more'])
        <div class="text-center mt-8">
            <button wire:click="loadMore" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Load More Elections
            </button>
        </div>
        @endif
    @else
        <!-- No Elections -->
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Elections Found</h3>
            <p class="text-gray-600">There are currently no elections matching your filter criteria.</p>
        </div>
    @endif
</div>