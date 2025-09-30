@extends('layouts.guest')

@section('title', 'Election Results - AYApoll')

@section('main-class', 'pt-16 overflow-x-hidden')

@section('content')
<!-- Header -->
<div class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <div class="text-center">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Election Results</h1>
            <p class="text-sm sm:text-base text-gray-600">View results and verify your vote</p>
        </div>
    </div>
</div>

<!-- Verification Card -->
<div class="bg-gray-50 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="text-center">
                <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6">Enter your receipt hash to verify your vote was recorded correctly</p>
                <div class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
                    <input type="text" placeholder="Enter receipt hash..." 
                           class="flex-1 px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button class="bg-blue-600 text-white px-4 sm:px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-medium text-sm sm:text-base whitespace-nowrap">
                        Verify Vote
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Active Elections -->
    <div class="mb-8 sm:mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Active Elections</h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800 self-start sm:self-auto">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                {{ $activeElections->count() }} Live
            </span>
        </div>
        
        @if($activeElections->isEmpty())
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <p class="text-gray-500">No active elections at this time</p>
            </div>
        @else
            <div class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-5xl mx-auto">
                @foreach($activeElections as $election)
                    @php
                        $turnout = $election->getVoterTurnout();
                        $timeRemaining = $election->getTimeRemaining();
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 hover:shadow-md transition-shadow overflow-hidden">
                        <div class="flex items-start justify-between gap-2 sm:gap-3 mb-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm sm:text-lg font-semibold text-gray-900 mb-1 truncate">{{ $election->title }}</h3>
                                <p class="text-xs sm:text-sm text-gray-600 line-clamp-2">{{ Str::limit($election->description, 60) }}</p>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-medium shrink-0">LIVE</span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">Progress</span>
                                <span class="font-medium">{{ number_format($turnout['total_voted']) }} / {{ number_format($turnout['total_eligible']) }} votes</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $turnout['percentage'] }}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between text-sm mb-2">
                                <div class="font-semibold" x-data="{ 
                                    endTime: new Date('{{ $election->alpine_timer['end_time'] }}').getTime(),
                                    timeLeft: '{{ $election->alpine_timer['display'] }}'
                                }" x-init="
                                    setInterval(() => {
                                        const now = new Date().getTime();
                                        const distance = endTime - now;
                                        if (distance > 0) {
                                            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                            
                                            if (days > 0) {
                                                timeLeft = days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
                                            } else if (hours > 0) {
                                                timeLeft = hours + 'h ' + minutes + 'm ' + seconds + 's';
                                            } else if (minutes > 0) {
                                                timeLeft = minutes + 'm ' + seconds + 's';
                                            } else {
                                                timeLeft = seconds + 's';
                                            }
                                        } else {
                                            const elapsed = Math.abs(distance);
                                            const days = Math.floor(elapsed / (1000 * 60 * 60 * 24));
                                            const hours = Math.floor((elapsed % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                            const minutes = Math.floor((elapsed % (1000 * 60 * 60)) / (1000 * 60));
                                            if (days > 0) {
                                                timeLeft = days + ' day' + (days > 1 ? 's' : '') + ' ago';
                                            } else if (hours > 0) {
                                                timeLeft = hours + ' hour' + (hours > 1 ? 's' : '') + ' ago';
                                            } else {
                                                timeLeft = minutes + ' minute' + (minutes > 1 ? 's' : '') + ' ago';
                                            }
                                        }
                                    }, 1000);
                                ">
                                    <span class="text-gray-500" x-text="timeLeft.includes('ago') ? 'Ended' : 'Ends in'">{{ $election->alpine_timer['ended'] ? 'Ended' : 'Ends in' }}</span> 
                                    <span class="text-red-600" x-text="timeLeft">{{ $election->alpine_timer['display'] }}</span>
                                </div>
                                <a href="{{ route('observer.election-results', $election->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">View Results →</a>
                            </div>
                            <div class="text-xs text-gray-400">
                                <div>Started: {{ $election->starts_at->format('M j, Y g:i A') }}</div>
                                <div>Ends: {{ $election->ends_at->format('M j, Y g:i A') }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    <!-- Completed Elections -->
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 mb-4 sm:mb-6">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Published Results</h2>
            <span class="text-xs sm:text-sm text-gray-500">{{ $completedElections->count() }} elections completed</span>
        </div>
        
        @if($completedElections->isEmpty())
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <p class="text-gray-500">No published election results available</p>
            </div>
        @else
            <div class="space-y-4 sm:space-y-6">
                @foreach($completedElections as $election)
                    @php
                        $turnout = $election->getVoterTurnout();
                        $firstPosition = $election->positions->first();
                        $positionTallies = $firstPosition ? $election->voteTallies->where('position_id', $firstPosition->id) : collect();
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $election->title }}</h3>
                                    <p class="text-sm text-gray-600">Completed {{ $election->ends_at->format('F j, Y') }}</p>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">PUBLISHED</span>
                                    <a href="{{ route('observer.election-results', $election->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 sm:p-6">
                            <div class="grid gap-6 lg:grid-cols-2">
                                <!-- Results Preview -->
                                <div>
                                    @if($firstPosition && $positionTallies->isNotEmpty())
                                        <h4 class="font-medium text-gray-900 mb-4">{{ $firstPosition->title }} Results</h4>
                                        <div class="space-y-3">
                                            @foreach($positionTallies->sortByDesc('vote_count')->take(3) as $tally)
                                                @php
                                                    $percentage = $turnout['total_voted'] > 0 ? round(($tally->vote_count / $turnout['total_voted']) * 100, 1) : 0;
                                                @endphp
                                                <div class="flex items-center justify-between">
                                                    <span class="text-gray-700 text-sm sm:text-base">{{ $tally->candidate->user->first_name }} {{ $tally->candidate->user->last_name }}</span>
                                                    <div class="flex items-center gap-2 sm:gap-3">
                                                        <div class="w-16 sm:w-20 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        <span class="text-sm font-medium w-8 sm:w-10">{{ $percentage }}%</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center text-gray-500">
                                            <p>Results processing completed</p>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Stats -->
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-4">Election Statistics</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm sm:text-base">
                                            <span class="text-gray-600">Total Votes</span>
                                            <span class="font-medium">{{ number_format($turnout['total_voted']) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm sm:text-base">
                                            <span class="text-gray-600">Turnout</span>
                                            <span class="font-medium">{{ $turnout['percentage'] }}%</span>
                                        </div>
                                        <div class="flex justify-between text-sm sm:text-base">
                                            <span class="text-gray-600">Eligible Voters</span>
                                            <span class="font-medium">{{ number_format($turnout['total_eligible']) }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm sm:text-base">
                                            <span class="text-gray-600">Positions</span>
                                            <span class="font-medium">{{ $election->positions->count() }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm sm:text-base">
                                            <span class="text-gray-600">Integrity</span>
                                            <span class="font-medium text-green-600">✓ Verified</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600 py-12 sm:py-16">
    <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-bold text-white mb-3 sm:mb-4">Join Future Elections</h2>
        <p class="text-lg sm:text-xl text-blue-100 mb-6 sm:mb-8">Register to participate in upcoming democratic processes</p>
        <a href="{{ route('auth.register') }}" 
           class="bg-white text-blue-600 hover:bg-gray-100 px-6 sm:px-8 py-2 sm:py-3 rounded-lg text-base sm:text-lg font-semibold transition inline-block">
            Register to Vote
        </a>
    </div>
</div>



@endsection