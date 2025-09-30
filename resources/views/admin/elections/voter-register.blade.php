@extends('layouts.admin')

@section('title', 'Voter Register - ' . $election->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between text-center md:text-left">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Voter Register</h1>
                <p class="mt-1 text-sm text-gray-600">{{ $election->title }}</p>
            </div>
            <a href="{{ route('admin.elections.show', $election) }}" 
               class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 mt-4 md:mt-0 mx-auto md:mx-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Election
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 gap-4 md:gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 md:p-5 text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-6 h-6 md:w-8 md:h-8 bg-blue-500 rounded-md flex items-center justify-center mr-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs md:text-sm font-medium text-gray-500">Total Accredited</span>
                </div>
                <div class="text-lg md:text-xl font-bold text-gray-900">{{ $voters->total() }}</div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-4 md:p-5 text-center">
                <div class="flex items-center justify-center mb-2">
                    <div class="w-6 h-6 md:w-8 md:h-8 bg-purple-500 rounded-md flex items-center justify-center mr-2">
                        <svg class="w-4 h-4 md:w-5 md:h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <span class="text-xs md:text-sm font-medium text-gray-500">Turnout Rate</span>
                </div>
                <div class="text-lg md:text-xl font-bold text-gray-900">
                    {{ $voters->total() > 0 ? round(($election->voteTokens()->where('is_used', true)->count() / $voters->total()) * 100, 1) : 0 }}%
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <div class="max-w-md mx-auto md:mx-0">
            <input type="text" 
                   id="voterSearch" 
                   placeholder="Search by name, email, or phone..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>
    </div>

    <!-- Voter List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Accredited Voters</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">List of all voters with valid tokens for this election</p>
        </div>
        
        @if($voters->count() > 0)
            <ul class="divide-y divide-gray-200" id="voterList">
                @foreach($voters as $voteToken)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ strtoupper(substr($voteToken->user->first_name, 0, 1) . substr($voteToken->user->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-xs md:text-sm font-medium text-gray-900">
                                            {{ $voteToken->user->first_name }} {{ $voteToken->user->last_name }}
                                        </p>
                                        @if(\App\Models\Candidate\Candidate::where('user_id', $voteToken->user->id)->where('status', 'approved')->exists())
                                            <span class="ml-1 md:ml-2 inline-flex items-center px-1.5 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Candidate
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 md:hidden">Registered {{ $voteToken->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="text-right">
                                    <p class="text-xs text-gray-500 hidden md:block">Token Status</p>
                                    @if($voteToken->is_used)
                                        <span class="inline-flex items-center px-1.5 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Voted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 md:px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            Pending
                                        </span>
                                    @endif
                                </div>
                                <div class="text-right hidden md:block">
                                    <p class="text-xs text-gray-500">Registered</p>
                                    <p class="text-xs text-gray-900">{{ $voteToken->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            
            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $voters->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No voters registered</h3>
                <p class="mt-1 text-sm text-gray-500">Publish the voter register to generate vote tokens.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('voterSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    const voterItems = document.querySelectorAll('#voterList li');
    let visibleCount = 0;
    
    voterItems.forEach(item => {
        const nameElement = item.querySelector('.text-xs.md\\:text-sm.font-medium.text-gray-900');
        const name = nameElement ? nameElement.textContent.toLowerCase() : '';
        
        const isVisible = searchTerm === '' || name.includes(searchTerm);
        item.style.display = isVisible ? 'block' : 'none';
        if (isVisible) visibleCount++;
    });
    
    // Show/hide no results message
    let noResultsMsg = document.getElementById('noResultsMessage');
    if (visibleCount === 0 && searchTerm !== '') {
        if (!noResultsMsg) {
            noResultsMsg = document.createElement('li');
            noResultsMsg.id = 'noResultsMessage';
            noResultsMsg.className = 'px-4 py-8 text-center text-gray-500';
            noResultsMsg.innerHTML = 'No voters found matching your search.';
            document.getElementById('voterList').appendChild(noResultsMsg);
        }
        noResultsMsg.style.display = 'block';
    } else if (noResultsMsg) {
        noResultsMsg.style.display = 'none';
    }
});
</script>
@endpush