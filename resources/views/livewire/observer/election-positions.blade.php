<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <button wire:click="backToElections" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Elections
            </button>
            <h1 class="text-2xl font-bold text-gray-900 mb-1">
                {{ $election->title }} 
                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                    {{ $election->status->value === 'active' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $election->status->value === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $election->status->value === 'ended' ? 'bg-gray-100 text-gray-800' : '' }}
                    {{ $election->status->value === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ $election->status->label() }}
                </span>
            </h1>
            <p class="text-gray-600">{{ $election->positions->count() }} positions • {{ $election->candidates->count() }} candidates</p>
        </div>
    </div>

    <!-- Metrics -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200 text-center">
            <p class="text-xl font-semibold text-gray-900 mb-2">{{ $election->positions->count() }}</p>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-purple-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-500">Total Positions</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-4 border border-gray-200 text-center">
            <p class="text-xl font-semibold text-gray-900 mb-2">{{ $election->candidates->count() }}</p>
            <div class="flex items-center justify-center">
                <div class="w-4 h-4 bg-green-100 rounded flex items-center justify-center mr-1">
                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-500">Total Candidates</p>
            </div>
        </div>
    </div>

    <!-- Positions -->
    <div class="space-y-6">
        @foreach($election->positions as $position)
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $position->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $position->description }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>{{ $position->candidates->count() }} candidates</span>
                        <span>{{ $position->voteTallies->sum('vote_count') }} votes</span>
                    </div>
                </div>
            </div>
            
            <div class="p-4 sm:p-6">
                @if($position->candidates->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($position->candidates as $candidate)
                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors" x-data="{ expanded: false }">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="font-medium text-gray-900 truncate">{{ $candidate->user->first_name }} {{ $candidate->user->last_name }}</h4>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                        {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $candidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $candidate->status->value === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $candidate->status->value === 'suspended' ? 'bg-orange-100 text-orange-800' : '' }}">
                                        {{ $candidate->status->label() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($election->status->value === 'ended')
                                <div class="text-right">
                                    <div class="text-lg font-semibold text-gray-900">{{ $candidate->voteTallies->first()->vote_count ?? 0 }}</div>
                                    <div class="text-xs text-gray-500">votes</div>
                                </div>
                                @endif
                                <button @click="expanded = !expanded" class="p-1 rounded hover:bg-gray-200 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        @if($candidate->manifesto)
                        <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($candidate->manifesto, 100) }}</p>
                        @endif
                        
                        @if($election->status->value === 'ended' && $candidate->voteTallies->first())
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            @php
                                $totalVotes = $position->voteTallies->sum('vote_count');
                                $candidateVotes = $candidate->voteTallies->first()->vote_count ?? 0;
                                $percentage = $totalVotes > 0 ? round(($candidateVotes / $totalVotes) * 100, 1) : 0;
                            @endphp
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">{{ $percentage }}%</span>
                                <div class="flex-1 mx-2 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Expandable Metadata -->
                        <div x-show="expanded" x-collapse class="mt-3 pt-3 border-t border-gray-100">
                            <h5 class="text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Transparency Details</h5>
                            <dl class="grid grid-cols-1 gap-2 text-xs">
                                <div class="flex justify-between py-1 bg-blue-50 px-2 rounded">
                                    <dt class="font-medium text-blue-700">Applied:</dt>
                                    <dd class="text-blue-900">{{ $candidate->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @if($candidate->approved_at)
                                <div class="flex justify-between py-1 bg-green-50 px-2 rounded">
                                    <dt class="font-medium text-green-700">Approved:</dt>
                                    <dd class="text-green-900">{{ $candidate->approved_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @endif
                                @if($candidate->approver)
                                <div class="flex justify-between py-1 bg-green-50 px-2 rounded">
                                    <dt class="font-medium text-green-700">Approved By:</dt>
                                    <dd class="text-green-900">{{ $candidate->approver->name ?? $candidate->approver->email }}</dd>
                                </div>
                                @endif
                                @if($candidate->suspended_at)
                                <div class="flex justify-between py-1 bg-orange-50 px-2 rounded">
                                    <dt class="font-medium text-orange-700">Suspended:</dt>
                                    <dd class="text-orange-900">{{ $candidate->suspended_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @endif
                                @if($candidate->suspender)
                                <div class="flex justify-between py-1 bg-orange-50 px-2 rounded">
                                    <dt class="font-medium text-orange-700">Suspended By:</dt>
                                    <dd class="text-orange-900">{{ $candidate->suspender->name ?? $candidate->suspender->email }}</dd>
                                </div>
                                @endif
                                @if($candidate->suspension_reason)
                                <div class="flex justify-between py-1 bg-orange-50 px-2 rounded">
                                    <dt class="font-medium text-orange-700">Suspension Reason:</dt>
                                    <dd class="text-orange-900">{{ $candidate->suspension_reason }}</dd>
                                </div>
                                @endif
                                @if($candidate->rejection_reason)
                                <div class="flex justify-between py-1 bg-red-50 px-2 rounded">
                                    <dt class="font-medium text-red-700">Rejection Reason:</dt>
                                    <dd class="text-red-900">{{ $candidate->rejection_reason }}</dd>
                                </div>
                                @endif
                                <div class="flex justify-between py-1 bg-purple-50 px-2 rounded">
                                    <dt class="font-medium text-purple-700">Payment Status:</dt>
                                    <dd class="text-purple-900">{{ $candidate->payment_status->label() }}</dd>
                                </div>
                                @if($candidate->application_fee > 0)
                                <div class="flex justify-between py-1 bg-purple-50 px-2 rounded">
                                    <dt class="font-medium text-purple-700">Application Fee:</dt>
                                    <dd class="text-purple-900">₦{{ number_format($candidate->application_fee, 2) }}</dd>
                                </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No candidates</h3>
                    <p class="mt-1 text-sm text-gray-500">No candidates have applied for this position yet.</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>