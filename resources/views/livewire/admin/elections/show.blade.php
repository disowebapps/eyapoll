<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $election->title }}</h2>
        <p class="text-gray-600 mt-2">{{ $election->description }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Election Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium mb-4">Election Information</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Type</dt>
                        <dd class="text-sm text-gray-900">{{ $election->type->label() }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $election->getStatusColor() }}-100 text-{{ $election->getStatusColor() }}-800">
                                {{ $election->status->label() }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="text-sm text-gray-900">{{ $election->starts_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">End Date</dt>
                        <dd class="text-sm text-gray-900">{{ $election->ends_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Positions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium mb-4">Positions</h3>
                <div class="space-y-4">
                    @foreach($election->positions as $position)
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">{{ $position->title }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $position->description }}</p>
                        <div class="mt-2 text-sm text-gray-500">
                            Max Candidates: {{ $position->max_candidates }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium mb-4">Actions</h3>
                <div class="space-y-3">
                    @if($election->canBeEdited())
                        <a href="{{ route('admin.elections.edit', $election->id) }}" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center block">
                            Edit Election
                        </a>
                    @endif
                    
                    @if($election->canBeStarted())
                        <button wire:click="openStartElectionModal" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                            Start Election
                        </button>
                    @endif
                    
                    @if($election->canBeEnded())
                        <button wire:click="openEndElectionModal" class="w-full bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            End Election
                        </button>
                    @endif

                    @if(($election->isActive() || $election->isEnded() || $election->hasVotes()) && !$election->isScheduled())
                        <a href="{{ route('admin.elections.results', $election->id) }}" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-center block">
                            View Results
                        </a>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium mb-4">Statistics</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Candidates</span>
                        <span class="text-sm font-medium">{{ $election->candidates->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Total Votes</span>
                        <span class="text-sm font-medium">{{ $election->voteRecords ? $election->voteRecords->count() : 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-500">Positions</span>
                        <span class="text-sm font-medium">{{ $election->positions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- End Election Confirmation Modal -->
    @if($showEndElectionModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">End Election</h3>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">Are you sure you want to end this election?</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium">This action cannot be undone.</p>
                                <p>Once ended, the election cannot be restarted and results will be finalized.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeEndElectionModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button wire:click="confirmEndElection" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        End Election
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Start Election Confirmation Modal -->
    @if($showStartElectionModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9-4V8a3 3 0 016 0v2M5 12h14l-1 7H6l-1-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Start Election</h3>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-600 mb-4">Are you sure you want to start this election?</p>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">This will activate the election.</p>
                                <p>Voters will be able to cast their votes and the election cannot be edited.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button wire:click="closeStartElectionModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button wire:click="confirmStartElection" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Start Election
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>