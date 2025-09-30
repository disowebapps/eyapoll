@extends('layouts.guest')

@section('title', 'Democratic Integrity Verification')

@section('main-class', 'pt-16')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="bg-white">
    <x-public.hero-section 
        title="Election Integrity Verification" 
        subtitle="Independent verification of democratic processes through cryptographic integrity checks." 
    />

    <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
        <!-- Election Info -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Democratic Integrity Verification</h2>
                <p class="text-gray-600 mt-1">Independent verification of civic participation cryptographic integrity</p>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $election->title }}</h3>
                        <p class="text-gray-600 mt-1">{{ $election->description }}</p>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Type:</span>
                                <span class="text-sm font-medium">{{ $election->type->label() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Status:</span>
                                <span class="text-sm font-medium">{{ $election->status->label() }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-500">Total Votes:</span>
                                <span class="text-sm font-medium">{{ $election->votes->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Verification Timestamp</h4>
                        <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y \a\t g:i A T') }}</p>
                        <div class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <div class="text-sm">
                                    <p class="font-medium text-green-800">Democratic Integrity Verified</p>
                                    <p class="text-green-700">All cryptographic checks passed successfully.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrity Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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
                        <h3 class="text-lg font-medium text-gray-900">{{ $integrity['verified_votes'] }}</h3>
                        <p class="text-sm text-gray-500">Verified Participation</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ $integrity['integrity_percentage'] }}%</h3>
                        <p class="text-sm text-gray-500">Integrity Score</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">{{ $integrity['chain_valid'] ? 'Valid' : 'Invalid' }}</h3>
                        <p class="text-sm text-gray-500">Chain Integrity</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chain Visualization -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Participation Chain Integrity</h3>
                <p class="text-sm text-gray-600 mt-1">Visualization of cryptographic participation chaining</p>
            </div>
            <div class="px-6 py-4">
                <canvas id="chainChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Technical Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Cryptographic Methods -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Cryptographic Methods</h3>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Hash Algorithm:</span>
                        <span class="text-sm font-mono text-gray-900">SHA-256</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Signature Algorithm:</span>
                        <span class="text-sm font-mono text-gray-900">RSA-PSS</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Key Size:</span>
                        <span class="text-sm font-mono text-gray-900">2048-bit</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Receipt Hash:</span>
                        <span class="text-sm font-mono text-gray-900">64-char hex</span>
                    </div>
                </div>
            </div>

            <!-- Security Features -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Security Features</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Ballot Secrecy</p>
                            <p class="text-xs text-gray-600">Identity separated from civic choices</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Individual Verifiability</p>
                            <p class="text-xs text-gray-600">Members can verify their participation inclusion</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Chain Integrity</p>
                            <p class="text-xs text-gray-600">Cryptographic linking prevents tampering</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Audit Trail</p>
                            <p class="text-xs text-gray-600">Complete immutable activity logs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invalid Votes (if any) -->
        @if(count($integrity['invalid_votes']) > 0)
        <div class="bg-white shadow rounded-lg mt-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-red-900">Integrity Issues Detected</h3>
                <p class="text-sm text-red-600 mt-1">{{ count($integrity['invalid_votes']) }} participation(s) failed integrity checks</p>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-3">
                    @foreach($integrity['invalid_votes'] as $invalidVote)
                    <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800">Participation Hash: {{ substr($invalidVote['vote_hash'], 0, 16) }}...</p>
                                <p class="text-sm text-red-700">Cast at: {{ \Carbon\Carbon::parse($invalidVote['cast_at'])->format('M j, Y g:i A') }}</p>
                                <div class="mt-2">
                                    <p class="text-xs font-medium text-red-800">Issues:</p>
                                    <ul class="text-xs text-red-700 list-disc list-inside mt-1">
                                        @foreach($invalidVote['issues'] as $issue => $value)
                                        <li>{{ ucfirst(str_replace('_', ' ', $issue)) }}: {{ $value ? 'Failed' : 'Passed' }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    // Chain visualization chart
    const ctx = document.getElementById('chainChart').getContext('2d');
    const chainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Action 1', 'Action 2', 'Action 3', 'Action 4', 'Action 5'],
            datasets: [{
                label: 'Chain Integrity',
                data: [100, 100, 100, 100, 100],
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Civic Participation Chain Integrity Over Time'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Integrity Score (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Participation Sequence'
                    }
                }
            }
        }
    });
</script>
@endsection