<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Election Results</h1>
        <p class="text-gray-600 mt-1">View published results for completed elections</p>
    </div>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Election</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Positions</th>
                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Votes</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($elections as $election)
                <tr class="hover:bg-gray-50 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">{{ $election->title }}</div>
                        <div class="text-sm text-gray-500">{{ $election->starts_at->format('M j, Y') }} - {{ $election->ends_at->format('M j, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800 ring-1 ring-gray-600/20' : 'bg-green-100 text-green-800 ring-1 ring-green-600/20' }}">
                            {{ $election->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-sm font-semibold text-gray-900">{{ $election->positions_count ?? 0 }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="text-sm font-semibold text-gray-900">{{ $election->vote_tokens_count ?? 0 }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click="viewElectionResults({{ $election->id }})" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            View Results
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="text-sm font-medium text-gray-900 mb-1">No results available</h3>
                            <p class="text-sm text-gray-500">Election results will appear here once elections are completed.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Cards -->
    <div class="lg:hidden space-y-4">
        @forelse($elections as $election)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <div class="flex items-start justify-between mb-3">
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-semibold text-gray-900">{{ $election->title }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ $election->starts_at->format('M j, Y') }} - {{ $election->ends_at->format('M j, Y') }}</p>
                </div>
                <span class="ml-3 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800' : 'bg-green-100 text-green-800' }}">
                    {{ $election->status->label() }}
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-900">{{ $election->positions_count ?? 0 }}</div>
                    <div class="text-xs text-gray-600">Positions</div>
                </div>
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-900">{{ $election->vote_tokens_count ?? 0 }}</div>
                    <div class="text-xs text-blue-600">Total Votes</div>
                </div>
            </div>
            
            <button wire:click="viewElectionResults({{ $election->id }})" 
                    class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                View Results
            </button>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <svg class="mx-auto w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No results available</h3>
            <p class="text-sm text-gray-500">Election results will appear here once elections are completed.</p>
        </div>
        @endforelse
    </div>

    @if($elections->hasPages())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mt-6">
        <div class="px-4 sm:px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-500">
                    Showing {{ $elections->firstItem() }} to {{ $elections->lastItem() }} of {{ $elections->total() }} elections
                </div>
                <div class="flex items-center space-x-2">
                    @if($elections->onFirstPage())
                        <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm">Previous</span>
                    @else
                        <button wire:click="previousPage" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">Previous</button>
                    @endif
                    
                    @if($elections->hasMorePages())
                        <button wire:click="nextPage" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm">Next</button>
                    @else
                        <span class="px-4 py-2 text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed text-sm">Next</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>