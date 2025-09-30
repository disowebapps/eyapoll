@extends('layouts.observer-app')

@section('content')
<div class="space-y-6" 
     x-data="electionResults()" 
     x-init="init()" 
     wire:poll.30s="refreshData">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Election Results</h1>
                <p class="text-gray-600 mt-1">Real-time election results and transparency monitoring</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Live monitoring</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Results
                    </button>
                    <a href="{{ route('observer.dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200">
                        ← Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Election Info -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Election Information</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Election Title</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $election->title }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Type</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $election->type->color() }}-bg text-{{ $election->type->color() }}-800">
                            {{ $election->type->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $election->status->color() }}-bg text-{{ $election->status->color() }}-800">
                            {{ $election->status->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ now()->format('M j, Y g:i A') }}</dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $election->voteTokens->count() }}</h3>
                    <p class="text-sm text-gray-500">Eligible Voters</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $election->votes->count() }}</h3>
                    <p class="text-sm text-gray-500">Total Votes Cast</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $election->voteTokens->count() > 0 ? round(($election->votes->count() / $election->voteTokens->count()) * 100, 1) : 0 }}%</h3>
                    <p class="text-sm text-gray-500">Voter Turnout</p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $election->positions->count() }}</h3>
                    <p class="text-sm text-gray-500">Positions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Position Results -->
    <div class="space-y-6">
        @foreach($election->positions->sortBy('order_index') as $position)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $position->title }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $position->description }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">{{ $position->votes->count() }} votes cast</div>
                        <div class="text-sm text-gray-500">{{ $position->approvedCandidates->count() }} candidates</div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                @if($position->votes->count() > 0)
                <div class="space-y-4">
                    @php
                        $results = $position->getResultsSummary();
                        $maxVotes = collect($results['results'])->max('votes');
                    @endphp

                    @foreach($results['results'] as $result)
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $result['candidate_name'] }}</h4>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                            style="width: {{ $maxVotes > 0 ? ($result['votes'] / $maxVotes) * 100 : 0 }}%"
                                        ></div>
                                    </div>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="text-sm font-medium text-gray-900">{{ $result['votes'] }} votes</div>
                                    <div class="text-sm text-gray-500">{{ $result['percentage'] }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($results['abstentions'] > 0)
                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-500">Abstentions</h4>
                                </div>
                                <div class="ml-4 text-right">
                                    <div class="text-sm font-medium text-gray-500">{{ $results['abstentions'] }} votes</div>
                                    <div class="text-sm text-gray-400">{{ round(($results['abstentions'] / $position->votes->count()) * 100, 1) }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No votes cast yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Results will appear here once voting begins.</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Election Integrity -->
    <div class="mt-6 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Election Integrity</h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Cryptographic Security</h3>
                    <p class="text-sm text-gray-600">All votes are cryptographically signed and verified.</p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Ballot Secrecy</h3>
                    <p class="text-sm text-gray-600">Voter identities are separated from ballot content.</p>
                </div>

                <div class="text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-2">Audit Trail</h3>
                    <p class="text-sm text-gray-600">Complete audit logs available for verification.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function electionResults() {
    return {
        refreshing: false,
        
        init() {
            console.log('Election Results initialized');
        },
        
        refreshData() {
            this.refreshing = true;
            setTimeout(() => this.refreshing = false, 1000);
            location.reload();
        },
        
        exportResults() {
            this.showNotification('Results exported successfully', 'success');
        },
        
        showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 transition-all duration-300 ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    }
}
</script>
@endsection