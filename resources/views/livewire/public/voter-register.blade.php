<div class="max-w-4xl mx-auto p-6">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Public Voter Register</h1>
        <p class="text-gray-600">View published voter registers for completed elections</p>
    </div>

    @if($elections->count() > 0)
        <div class="mt-6 bg-white rounded-lg shadow-sm border">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Accredited Voters' Register</h3>
                <div class="mt-4">
                    <select wire:model.live="selectedElection" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        @foreach($elections as $election)
                            <option value="{{ $election->id }}">{{ $election->title }} ({{ $election->starts_at->format('M d, Y') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="p-6">
                @if($selectedElection)
                    <div class="mb-4">
                        <input type="text" 
                               wire:model.live="search"
                               placeholder="Search by name or email..."
                               autocomplete="off"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                    </div>
                @endif
                
                @if($selectedElection && $verifiedVoters && is_array($verifiedVoters) && count($verifiedVoters) > 0)
                    <div class="mb-4 text-sm text-gray-600">
                        @if($search)
                            Showing <strong>{{ count($verifiedVoters) }}</strong> of <strong>{{ count($allVoters) }}</strong> accredited users
                        @else
                            Total Accredited Users: <strong>{{ count($verifiedVoters) }}</strong>
                        @endif
                    </div>
                    
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        @foreach($verifiedVoters as $voter)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $voter['name'] }}</p>
                                    <p class="text-sm text-gray-600">Registered: {{ $voter['registered_at'] }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full {{ $voter['status'] === 'Voted' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $voter['status'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 italic">Select an election to view its published voter register.</p>
                @endif
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Published Voter Registers</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Voter registers are published after elections are completed. Check back after upcoming elections conclude.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Back to Home
                </a>
                <a href="{{ route('voter.register') }}" class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                    Register to Vote
                </a>
            </div>
        </div>
    @endif
</div>