<div>

    
    <div x-data="{ showModal: @entangle('showConfirmation') }"
         x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="confirm-vote-title"
         aria-describedby="confirm-vote-description"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-start justify-center min-h-screen px-4 pt-20">
            <div class="fixed inset-0 bg-black opacity-50" @click="showModal = false" aria-hidden="true"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <div class="p-4 sm:p-6 text-center">
                    <h3 id="confirm-vote-title" class="text-lg font-semibold text-gray-900 mb-3">Confirm Vote</h3>

                    <div id="confirm-vote-description" class="space-y-2 mb-4" role="region" aria-labelledby="vote-summary">
                        <h4 id="vote-summary" class="sr-only">Your vote summary</h4>
                        @if($voteSummary)
                            @foreach($voteSummary as $summary)
                            <div class="text-sm">
                                <span class="font-medium text-gray-700">{{ $summary['position'] }}:</span>
                                @if($summary['is_abstention'])
                                    <span class="text-gray-500 ml-1">Abstained</span>
                                @else
                                    @foreach($summary['selections'] as $selection)
                                    <span class="text-green-600 ml-1 font-medium">{{ $selection }}</span>
                                    @endforeach
                                @endif
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded p-3 mb-4" role="alert">
                        <p class="text-red-700 text-sm font-medium">⚠️ Please review your final ballot. This cannot be changed once your vote is cast</p>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button @click="showModal = false"
                                class="flex-1 px-4 py-3 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]">
                            Review Vote
                        </button>
                        <button wire:click="submitVote"
                                class="flex-1 px-4 py-3 text-sm text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 min-h-[44px]"
                                aria-describedby="cast-vote-help">
                            Cast Vote
                        </button>
                        <div id="cast-vote-help" class="sr-only">Submit your final vote - this action cannot be undone</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('vote_message'))
    <div class="fixed top-4 right-4 z-50 bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded max-w-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)">
        {{ session('vote_message') }}
    </div>
    @endif

    @if($showSecurityLoader)
    <div class="fixed inset-0 bg-gray-50 z-[70] flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h6 class="text-xl font-semibold text-gray-900 mb-2">Preparing Voting Booth</h6>
                <p class="text-blue-600 font-medium">Loading secure interface...</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Abstain Confirmation Modal -->
    @if($showAbstainModal ?? false)
    <div x-data="{ showAbstainModal: @entangle('showAbstainModal') }"
         x-show="showAbstainModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         role="dialog"
         aria-modal="true"
         aria-labelledby="abstain-title"
         aria-describedby="abstain-description"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-start justify-center min-h-screen px-4 pt-20">
            <div class="fixed inset-0 bg-black opacity-50" @click="showAbstainModal = false" aria-hidden="true"></div>

            <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                <div class="p-4 sm:p-6 text-center">
                    <div class="w-12 h-12 mx-auto mb-4 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 id="abstain-title" class="text-lg font-semibold text-gray-900 mb-3">Confirm Abstention</h3>

                    <div id="abstain-description" class="space-y-2 mb-4" role="region">
                        <p class="text-sm text-gray-600">Are you sure you want to abstain from voting for this position?</p>
                        <div class="bg-orange-50 border border-orange-200 rounded p-3">
                            <p class="text-orange-700 text-sm font-medium">⚠️ This action cannot be undone. You won't be able to vote for any candidate in this position.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                        <button @click="showAbstainModal = false"
                                class="flex-1 px-4 py-3 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]">
                            Keep Voting
                        </button>
                        <button wire:click="confirmAbstain"
                                class="flex-1 px-4 py-3 text-sm text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 min-h-[44px]"
                                aria-describedby="abstain-help">
                            Confirm Abstention
                        </button>
                        <div id="abstain-help" class="sr-only">Skip voting for this position - this action cannot be undone</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Election Title -->


    <!-- Position Steps -->
    @if(count($positions) > 1)
    <nav class="mb-2" aria-label="Position navigation" style="position: -webkit-sticky; position: sticky; top: 78px; z-index: 20; background: white; padding: 6px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-bottom: 1px solid #e5e7eb;">
        <div class="flex items-center justify-center space-x-0.25 overflow-x-auto px-2" role="tablist">
            @foreach($positions as $index => $position)
            <button
                wire:click="goToPosition({{ $index }})"
                class="flex-shrink-0 w-6 h-6 sm:w-4 sm:h-4 rounded-full text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[20px] min-w-[20px] sm:min-h-[32px] sm:min-w-[32px] {{ $currentPositionIndex === $index ? 'bg-blue-600 text-white' : ($this->isPositionComplete($position['id']) ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-600 hover:bg-gray-400') }}"
                role="tab"
                aria-selected="{{ $currentPositionIndex === $index ? 'true' : 'false' }}"
                aria-label="Go to position {{ $index + 1 }}: {{ $position['title'] }}"
            >
                {{ $index + 1 }}
            </button>
            @if($index < count($positions) - 1)
            <div class="w-2 sm:w-2 h-0.5 bg-gray-300 flex-shrink-0" aria-hidden="true"></div>
            @endif
            @endforeach
        </div>
        <div class="text-center mt-4 px-4">
            <span class="text-xs sm:text-sm font-medium text-gray-500 bg-blue-100 px-2 py-1 sm:px-3 sm:py-1 rounded" role="status" aria-live="polite">
                <span class="hidden sm:inline">You voted</span>
                <span class="sm:hidden">Voted</span>
                <span class="text-blue-600 font-semibold">{{ $progress['completed'] }}/{{ $progress['total'] }}</span>
                <span class="hidden sm:inline">ballots</span>
            </span>
        </div>
    </nav>
    @endif

    <!-- No Positions Message -->
    @if(empty($positions))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Positions Available</h3>
        <p class="text-gray-500">This election has no active positions to vote for.</p>
        <a href="{{ route('voter.dashboard') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Back to Dashboard
        </a>
    </div>
    @elseif($this->currentPosition)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Position Header -->
        <div class="px-4 sm:px-8 py-4 sm:py-6 border-b border-gray-200 text-center md:text-left">
            <h2 class="text-xl sm:text-2xl font-bold text-blue-600 mb-2">{{ $this->currentPosition['title'] }}</h2>
            <p class="text-gray-600 text-xs sm:text-sm">Vote for your candidate</p>
        </div>

        <!-- Candidates -->
        <div class="p-4 sm:p-8">
            <div class="space-y-3" role="radiogroup" aria-labelledby="candidates-heading">
                <h2 id="candidates-heading" class="sr-only">Candidates for {{ $this->currentPosition['title'] }}</h2>
                @foreach($this->currentPosition['candidates'] as $candidate)
                @php
                    $selectedCandidates = $this->getSelectedCandidatesForPosition($this->currentPosition['id']);
                    $isSelected = in_array($candidate['id'], $selectedCandidates);
                    $hasSelection = !empty($selectedCandidates);
                    $canSelect = !$hasSelection || $isSelected;
                @endphp
                <div class="bg-white border-2 rounded-lg overflow-hidden transition-all duration-200 {{ $isSelected ? 'border-blue-500 shadow-md bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <button
                        wire:click="toggleCandidate({{ $this->currentPosition['id'] }}, {{ $candidate['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="toggleCandidate({{ $this->currentPosition['id'] }}, {{ $candidate['id'] }})"
                        class="w-full p-3 text-left focus:outline-none focus:ring-2 focus:ring-blue-500 {{ !$canSelect ? 'opacity-60 cursor-not-allowed' : 'hover:bg-gray-50' }}"
                        {{ !$canSelect ? 'disabled' : '' }}
                        role="radio"
                        aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                        aria-label="Vote for {{ $candidate['name'] }} {{ $isSelected ? '(selected)' : '' }}"
                    >
                        <div class="flex items-center space-x-3">
                            <!-- Candidate Photo -->
                            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-indigo-200 overflow-hidden shadow-md flex-shrink-0">
                                <img src="{{ app(\App\Services\AvatarService::class)->getAvatarUrl($candidate['name'], 48) }}"
                                       alt="Avatar of {{ $candidate['name'] }}"
                                       class="w-full h-full object-cover"
                                       loading="lazy">
                            </div>

                            <!-- Candidate Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $candidate['name'] }}</h3>
                                    <svg class="w-5 h-5 {{ $isSelected ? 'text-green-600' : 'text-gray-400' }} flex-shrink-0"
                                          fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a7.464 7.464 0 01-1.15 3.993m1.989 3.559A11.209 11.209 0 008.25 10.5a3.75 3.75 0 117.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 01-3.6 9.75m6.633-4.596a18.666 18.666 0 01-2.485 5.33" />
                                    </svg>
                                </div>
                                <span wire:loading wire:target="toggleCandidate({{ $this->currentPosition['id'] }}, {{ $candidate['id'] }})"
                                      class="text-green-600 text-xs block mt-3">Voting...</span>
                            </div>
                        </div>
                    </button>

                    @if($isSelected)
                    <div class="bg-green-50 px-3 sm:px-4 py-2 border-t border-blue-200" role="status" aria-live="polite">
                        <div class="flex items-center justify-center text-blue-700 text-xs font-medium">
                            <span class="hidden sm:inline">You Voted For This Candidate</span>
                            <span class="sm:hidden">You Voted For This Candidate</span>
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 ml-1 text-blue-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Skip Option -->
            <div class="mt-4 pb-3 border-t border-gray-200 text-center">
                <button
                    wire:click="showAbstainModal"
                    class="text-gray-500 hover:text-gray-700 text-sm underline focus:outline-none focus:ring-2 focus:ring-gray-500 rounded px-2 py-1 min-h-[44px]"
                    aria-label="Skip voting for this position"
                >
                    Skip this position
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Navigation -->
    <nav class="bg-white border-t border-gray-200 p-3 sm:p-4 md:relative md:rounded-lg md:border md:shadow-sm md:z-auto"
          style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 30;"
          aria-label="Voting navigation">
        <div class="flex items-center justify-center gap-2 sm:gap-4 lg:gap-8 max-w-4xl mx-auto">
            @if(!empty($positions) && $this->currentPosition)
                @if($currentPositionIndex > 0)
                <button
                    wire:click="previousPosition"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-3 border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                    aria-label="Go to previous position"
                >
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Previous
                </button>
                @else
                <a href="{{ route('voter.dashboard') }}"
                   class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-3 border border-gray-300 rounded-lg text-xs sm:text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                   aria-label="Return to dashboard">
                    <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back
                </a>
                @endif

                @if($this->currentPositionIndex < count($positions) - 1)
                <button
                    wire:click="nextPosition"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-2 py-3 bg-blue-600 border border-transparent rounded-lg text-xs sm:text-sm font-semibold text-white hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 min-h-[44px]"
                    aria-label="Go to next position"
                >
                    <span class="hidden sm:inline">Next Ballot</span>
                    <span class="sm:hidden">Next Ballot</span>
                    <svg class="w-4 h-4 ml-1 sm:ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
                @else
                <button
                    wire:click="showConfirmationModal"
                    class="flex-1 sm:flex-initial inline-flex items-center justify-center px-3 sm:px-4 py-3 bg-green-600 border border-transparent rounded-lg text-xs sm:text-sm font-bold text-white hover:bg-green-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 min-h-[44px]"
                    aria-label="Review and cast your final vote"
                >
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Cast Vote
                </button>
                @endif
            @else
                <a href="{{ route('voter.dashboard') }}"
                   class="flex-1 inline-flex items-center justify-center px-4 sm:px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 min-h-[44px]"
                   aria-label="Return to dashboard">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Dashboard
                </a>
                <div class="flex-1"></div>
            @endif
        </div>
    </nav>
</div>

    <!-- Confirmation Modal -->
    @if($showConfirmation)
    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 99999; display: flex; align-items: center; justify-content: center;">
        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; width: 90%;">
            <h3>Confirm Your Vote</h3>
            <p>Are you sure you want to cast your vote?</p>
            <button wire:click="submitVote" style="background: green; color: white; padding: 10px 20px; margin: 10px;" aria-label="Confirm and cast your vote">Cast Vote</button>
            <button wire:click="hideConfirmationModal" style="background: gray; color: white; padding: 10px 20px; margin: 10px;" aria-label="Cancel vote submission">Cancel</button>
        </div>
    </div>
    @endif
    <script>
        document.addEventListener('livewire:loading', () => {
            // Show saving indicator when any Livewire request starts
            const savingIndicator = document.querySelector('[x-data*="saving"]');
            if (savingIndicator && savingIndicator.__x) {
                savingIndicator.__x.$data.saving = true;
                savingIndicator.__x.$data.saved = false;
            }
        });

        document.addEventListener('livewire:loaded', () => {
            // Hide saving and show saved indicator when request completes
            const savingIndicator = document.querySelector('[x-data*="saving"]');
            if (savingIndicator && savingIndicator.__x) {
                savingIndicator.__x.$data.saving = false;
                savingIndicator.__x.$data.saved = true;

                // Hide saved indicator after 2 seconds
                setTimeout(() => {
                    savingIndicator.__x.$data.saved = false;
                }, 2000);
            }
        });
    </script>

</div>

