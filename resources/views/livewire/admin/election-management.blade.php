@push('styles')
<style>
[x-cloak] { display: none !important; }
</style>
@endpush

<div class="max-w-6xl mx-auto p-4 md:p-6 space-y-4 md:space-y-6 text-center md:text-left" wire:poll.5s="refreshElection">
    <!-- Election Header -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start space-y-3 md:space-y-0 items-center md:items-start">
            <div class="flex-1">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $election->title }}</h1>
                <p class="text-gray-600 mt-1 text-sm md:text-base">{{ $election->description }}</p>
            </div>
            <div class="flex-shrink-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs md:text-sm font-medium 
                    {{ $election->phase?->value === 'voting' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $election->phase?->label() ?? 'Setup' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Phase Management -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Phase Management</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 text-center lg:text-left">
            <!-- Current Phase Info -->
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Current Phase</h3>
                <div class="bg-gray-50 p-3 md:p-4 rounded-lg">
                    <p class="font-medium text-sm md:text-base">{{ $election->phase?->label() ?? 'Setup' }}</p>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">
                        @if($election->phase?->value === 'voting')
                            Voting is active - voters can cast ballots
                        @elseif($election->phase?->value === 'verification')
                            Voter register published - ready for voting
                        @else
                            {{ $election->phase?->label() ?? 'Election setup in progress' }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Available Transitions -->
            <div>
                <h3 class="font-medium text-gray-900 mb-2">Available Actions</h3>
                <div class="space-y-2" x-data="{ showModal: false, selectedPhase: '', phaseLabel: '' }">
                    @foreach($availableTransitions as $phase)
                        <button @click="selectedPhase = '{{ $phase->value }}'; phaseLabel = '{{ $phase->label() }}'; showModal = true"
                                class="w-full text-center px-3 md:px-4 py-2 rounded-lg text-xs md:text-sm font-medium
                                    @if($phase->value === 'candidate_registration_closed')
                                        bg-red-50 hover:bg-red-100 text-red-700
                                    @else
                                        bg-blue-50 hover:bg-blue-100 text-blue-700
                                    @endif">
                            → {{ $phase->label() }}
                        </button>
                    @endforeach
                    
                    @if(empty($availableTransitions))
                        <p class="text-xs md:text-sm text-gray-500 italic">No transitions available from current phase</p>
                    @endif

                    <!-- Phase Transition Modal -->
                    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                        <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                    <div class="sm:flex sm:items-start">
                                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" x-bind:class="selectedPhase === 'candidate_registration_closed' ? 'bg-red-100' : 'bg-blue-100'">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-bind:class="selectedPhase === 'candidate_registration_closed' ? 'text-red-600' : 'text-blue-600'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                Confirm Phase Transition
                                            </h3>
                                            <div class="mt-2">
                                                <p class="text-sm text-gray-500">
                                                    Are you sure you want to transition this election to <strong x-text="phaseLabel"></strong>? This action cannot be undone.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                    <button @click="$wire.transitionPhase(selectedPhase); showModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                                        x-bind:class="selectedPhase === 'candidate_registration_closed' ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500' : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'">
                                        <span x-text="selectedPhase === 'candidate_registration_closed' ? 'Confirm Close' : 'Confirm Transition'"></span>
                                    </button>
                                    <button @click="showModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Voter Register Management -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 text-center sm:text-left">
            <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                <h2 class="text-lg font-semibold text-gray-900">Voter Register</h2>
                @if($election->voter_register_published)
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mx-auto sm:mx-0">
                        ✅ Register Published
                    </span>
                @endif
            </div>
            @if($election->voteTokens()->count() > 0)
                <a href="{{ route('admin.elections.voter-register.view', $election) }}" 
                   class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mt-2 sm:mt-0 mx-auto sm:mx-0">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Register
                </a>
            @endif
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 mb-4 text-center lg:text-left">
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg">
                <p class="text-xs md:text-sm text-gray-600">Registration Period</p>
                <p class="font-medium text-sm md:text-base">
                    @if($election->voter_register_starts && $election->voter_register_ends)
                        {{ $election->voter_register_starts->format('M d') }} - {{ $election->voter_register_ends->format('M d, Y') }}
                    @else
                        Not set
                    @endif
                </p>
            </div>
            
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg">
                <p class="text-xs md:text-sm text-gray-600">Accredited Users</p>
                <p class="font-medium text-sm md:text-base">{{ $election->voteTokens()->count() }}</p>
            </div>
            
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg sm:col-span-2 lg:col-span-1">
                <p class="text-xs md:text-sm text-gray-600">Eligible Voters</p>
                <p class="font-medium text-sm md:text-base">{{ $eligibleVotersCount }}</p>
            </div>
        </div>

        <!-- Voter Register Controls -->
        <div class="overflow-x-auto" x-data="{ showPublishModal: false, showExtendModal: false, showRestartModal: false }">
            @if($election->voter_register_ends && $election->voter_register_ends <= now())
                <div class="flex flex-col sm:flex-row gap-2">
                    <button @click="showPublishModal = true" class="flex-1 sm:flex-initial w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                        📋 {{ $election->voter_register_published ? 'Republish' : 'Publish' }} Voter Register
                    </button>
                    <button @click="showRestartModal = true" class="flex-1 sm:flex-initial w-full sm:w-auto bg-orange-600 hover:bg-orange-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                        🔄 Restart Registration
                    </button>
                </div>
            @else
                <button @click="showExtendModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                    ⏰ Extend Registration
                </button>
            @endif

            <!-- Publish Modal -->
            <div x-show="showPublishModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showPublishModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPublishModal = false"></div>
                    <div x-show="showPublishModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Publish Voter Register</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <h4 class="font-medium text-blue-900">Voter Accreditation Summary</h4>
                                </div>
                                <p class="text-sm text-blue-800 mb-2">
                                    <strong>{{ $eligibleVotersCount }}</strong> eligible voters will be assigned vote tokens and accredited to vote in this election.
                                </p>
                                <p class="text-xs text-blue-700">
                                    This includes {{ \App\Models\User::where('status', 'approved')->count() }} approved users and {{ \App\Models\Candidate\Candidate::where('status', 'approved')->count() }} approved candidates.
                                </p>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">This will finalize the voter register and transition to verification phase. This action cannot be undone.</p>
                            <div class="flex justify-end space-x-3">
                                <button @click="showPublishModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                <form method="POST" action="{{ route('admin.elections.voter-register.publish', $election) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Confirm</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extend Modal -->
            <div x-show="showExtendModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showExtendModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showExtendModal = false"></div>
                    <div x-show="showExtendModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Extend Registration</h3>
                            <p class="text-sm text-gray-500 mb-4">Select new end date for voter registration period.</p>
                            <form method="POST" action="{{ route('admin.elections.voter-register.extend', $election) }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="extension_date" class="block text-sm font-medium text-gray-700 mb-2">New Registration End Date</label>
                                    <input type="datetime-local" 
                                           id="extension_date" 
                                           name="extension_date" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           required>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="showExtendModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Confirm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restart Modal -->
            <div x-show="showRestartModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showRestartModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showRestartModal = false"></div>
                    <div x-show="showRestartModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Restart Registration</h3>
                            <p class="text-sm text-gray-500 mb-4">This will reopen registration with a new deadline.</p>
                            <form method="POST" action="{{ route('admin.elections.voter-register.restart', $election) }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="restart_date" class="block text-sm font-medium text-gray-700 mb-2">New Registration Deadline</label>
                                    <input type="datetime-local" 
                                           id="restart_date" 
                                           name="restart_date" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}"
                                           required>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="showRestartModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">Restart Registration</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidate Application Management -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 text-center sm:text-left">
            <h2 class="text-lg font-semibold text-gray-900">Candidate Applications</h2>
            @if($election->candidate_register_starts && $election->candidate_register_ends && $election->candidate_register_ends > now())
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-2 sm:mt-0 mx-auto sm:mx-0">
                    ✅ Applications Open
                </span>
            @endif
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 mb-4 text-center lg:text-left">
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg">
                <p class="text-xs md:text-sm text-gray-600">Application Period</p>
                <p class="font-medium text-sm md:text-base">
                    @if($election->candidate_register_starts && $election->candidate_register_ends)
                        {{ $election->candidate_register_starts->format('M d') }} - {{ $election->candidate_register_ends->format('M d, Y') }}
                    @else
                        Not set
                    @endif
                </p>
            </div>
            
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg">
                <p class="text-xs md:text-sm text-gray-600">Total Applications</p>
                <p class="font-medium text-sm md:text-base">{{ $election->candidates()->count() }}</p>
            </div>
            
            <div class="bg-gray-50 p-3 md:p-4 rounded-lg sm:col-span-2 lg:col-span-1">
                <p class="text-xs md:text-sm text-gray-600">Approved Candidates</p>
                <p class="font-medium text-sm md:text-base">{{ $election->candidates()->where('status', 'approved')->count() }}</p>
            </div>
        </div>

        <!-- Candidate Application Controls -->
        <div class="overflow-x-auto" x-data="{ showExtendCandidateModal: false, showRestartCandidateModal: false, showSetPeriodModal: false, showPublishCandidatesModal: false }">
            @if(!$election->candidate_register_starts || !$election->candidate_register_ends)
                <button @click="showSetPeriodModal = true" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                    📅 Set Application Period
                </button>
            @elseif($election->candidate_register_ends && $election->candidate_register_ends <= now() && !$election->candidate_list_published)
                <div class="flex flex-col sm:flex-row gap-2">
                    <button @click="showPublishCandidatesModal = true" class="flex-1 sm:flex-initial w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                        📋 Publish Candidate List
                    </button>
                    <button @click="showRestartCandidateModal = true" class="flex-1 sm:flex-initial w-full sm:w-auto bg-orange-600 hover:bg-orange-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                        🔄 Restart Applications
                    </button>
                </div>
            @elseif($election->candidate_list_published)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    ✅ Candidate List Published
                </span>
            @elseif($election->candidate_register_starts && $election->candidate_register_ends && $election->candidate_register_ends > now())
                <button @click="showExtendCandidateModal = true" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium">
                    ⏰ Extend Applications
                </button>
            @endif

            <!-- Extend Candidate Applications Modal -->
            <div x-show="showExtendCandidateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showExtendCandidateModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showExtendCandidateModal = false"></div>
                    <div x-show="showExtendCandidateModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Extend Candidate Applications</h3>
                            <p class="text-sm text-gray-500 mb-4">Select new end date for candidate application period.</p>
                            <form method="POST" action="{{ route('admin.elections.candidate-register.extend', $election) }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="candidate_extension_date" class="block text-sm font-medium text-gray-700 mb-2">New Application End Date</label>
                                    <input type="datetime-local" 
                                           id="candidate_extension_date" 
                                           name="extension_date" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           required>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="showExtendCandidateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Confirm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Set Application Period Modal -->
            <div x-show="showSetPeriodModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showSetPeriodModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showSetPeriodModal = false"></div>
                    <div x-show="showSetPeriodModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Set Application Period</h3>
                            <form method="POST" action="{{ route('admin.elections.candidate-register.set', $election) }}">
                                @csrf
                                <div class="space-y-4">
                                    <div>
                                        <label for="candidate_start_date" class="block text-sm font-medium text-gray-700 mb-2">Applications Start</label>
                                        <input type="datetime-local" 
                                               id="candidate_start_date" 
                                               name="start_date" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               min="{{ now()->format('Y-m-d\TH:i') }}"
                                               required>
                                    </div>
                                    <div>
                                        <label for="candidate_end_date" class="block text-sm font-medium text-gray-700 mb-2">Applications End</label>
                                        <input type="datetime-local" 
                                               id="candidate_end_date" 
                                               name="end_date" 
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                               min="{{ now()->format('Y-m-d\TH:i') }}"
                                               required>
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3 mt-6">
                                    <button type="button" @click="showSetPeriodModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Set Period</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Publish Candidate List Modal -->
            <div x-show="showPublishCandidatesModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showPublishCandidatesModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPublishCandidatesModal = false"></div>
                    <div x-show="showPublishCandidatesModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Publish Candidate List</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <h4 class="font-medium text-blue-900">Candidate List Summary</h4>
                                </div>
                                <p class="text-sm text-blue-800 mb-2">
                                    <strong>{{ $election->candidates()->where('status', 'approved')->count() }}</strong> approved candidates will be published for public viewing.
                                </p>
                                <p class="text-xs text-blue-700">
                                    This list will be visible to admins, observers, and the public.
                                </p>
                            </div>
                            <p class="text-sm text-gray-500 mb-4">This will make the final candidate list publicly available. This action cannot be undone.</p>
                            <div class="flex justify-end space-x-3">
                                <button @click="showPublishCandidatesModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                <form method="POST" action="{{ route('admin.elections.candidate-list.publish', $election) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">Publish List</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Restart Candidate Applications Modal -->
            <div x-show="showRestartCandidateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-start justify-center min-h-screen pt-16 px-4 pb-20 text-center">
                    <div x-show="showRestartCandidateModal" x-transition class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showRestartCandidateModal = false"></div>
                    <div x-show="showRestartCandidateModal" x-transition class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-lg w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Restart Candidate Applications</h3>
                            <p class="text-sm text-gray-500 mb-4">This will reopen candidate applications with a new deadline.</p>
                            <form method="POST" action="{{ route('admin.elections.candidate-register.restart', $election) }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="candidate_restart_date" class="block text-sm font-medium text-gray-700 mb-2">New Application Deadline</label>
                                    <input type="datetime-local" 
                                           id="candidate_restart_date" 
                                           name="restart_date" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                           min="{{ now()->format('Y-m-d\TH:i') }}"
                                           value="{{ now()->addDays(7)->format('Y-m-d\TH:i') }}"
                                           required>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button" @click="showRestartCandidateModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Cancel</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">Restart Applications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Candidates -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Candidates</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 md:gap-4 text-center sm:text-left">
            @forelse($election->candidates as $candidate)
                <div class="border rounded-lg p-3 md:p-4">
                    <h3 class="font-medium text-sm md:text-base">{{ $candidate->user->first_name }} {{ $candidate->user->last_name }}</h3>
                    <p class="text-xs md:text-sm text-gray-600">{{ $candidate->position?->title }}</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ ucfirst($candidate->status->value ?? $candidate->status) }}
                        </span>
                        <a href="{{ route('admin.candidates.show', $candidate->id) }}" 
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            View Profile
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 italic col-span-full text-sm">No candidates registered yet</p>
            @endforelse
        </div>
    </div>

    <!-- Election Timeline -->
    <div class="bg-white rounded-lg shadow-sm border p-4 md:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Election Timeline</h2>
        
        @php
            $phases = [
                'setup' => ['Setup & Configuration', 'Initial election setup and configuration'],
                'registration' => ['Registration Period', 'Candidate and voter registration'],
                'verification' => ['Verification & Voter Register', 'Candidate verification and voter register publication'],
                'voting' => ['Voting Period', 'Active voting phase'],
                'collation' => ['Results Collation', 'Vote counting and verification'],
                'results_published' => ['Results Published', 'Final results available to public'],
                'post_election' => ['Post Election Activities', 'Appeals, audits and final certifications'],
                'archived' => ['Election Archival', 'Election data archived and stored']
            ];
            
            $currentPhase = $election->phase?->value;
            $phaseOrder = ['setup', 'registration', 'verification', 'voting', 'collation', 'results_published', 'post_election', 'archived'];
            $currentIndex = array_search($currentPhase === 'candidate_registration' || $currentPhase === 'voter_registration' ? 'registration' : $currentPhase, $phaseOrder);
        @endphp
        
        <div class="space-y-4 text-left">
            @foreach($phases as $phaseKey => $phaseData)
                @php
                    $phaseIndex = array_search($phaseKey, $phaseOrder);
                    $isCompleted = $phaseIndex < $currentIndex;
                    $isCurrent = ($phaseKey === 'registration' && in_array($currentPhase, ['candidate_registration', 'voter_registration'])) || $phaseKey === $currentPhase;
                    $isUpcoming = $phaseIndex > $currentIndex;
                @endphp
                
                <div class="flex items-start group relative">
                    @if(!$loop->last)
                        <div class="absolute left-2 top-6 w-0.5 h-6 {{ $isCompleted ? 'bg-green-300' : 'bg-gray-200' }} transition-colors duration-200"></div>
                    @endif
                    
                    <div class="w-4 h-4 rounded-full mr-4 flex-shrink-0 transition-all duration-200 relative z-10
                        @if($isCompleted)
                            bg-green-500 ring-4 ring-green-100
                        @elseif($isCurrent)
                            bg-blue-500 ring-4 ring-blue-100 animate-pulse
                        @else
                            bg-gray-300 border-2 border-gray-200
                        @endif">
                        @if($isCompleted)
                            <svg class="w-2 h-2 text-white absolute top-1 left-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </div>
                    
                    <div class="flex-1">
                        <span class="text-sm font-medium
                            @if($isCompleted)
                                text-green-700
                            @elseif($isCurrent)
                                text-blue-700
                            @else
                                text-gray-500
                            @endif">
                            {{ $phaseData[0] }}
                            @if($isCurrent)
                                <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">Current</span>
                            @endif
                        </span>
                        <p class="text-xs mt-0.5 text-gray-500">
                            {{ $phaseData[1] }}
                        </p>
                        
                        @if($phaseKey === 'voting' && $election->starts_at && $election->ends_at)
                            <div class="text-xs mt-1 font-medium
                                @if($isCurrent)
                                    text-blue-600
                                @else
                                    text-gray-500
                                @endif">
                                {{ $election->starts_at->format('M d, H:i') }} - {{ $election->ends_at->format('M d, H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>