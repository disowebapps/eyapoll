<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg border p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">🔒 Vote Token Monitor</h1>
                <p class="text-sm text-gray-500">Real-time monitoring of vote token creation and usage</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-500">Total Tokens</div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</div>
                <div class="text-sm text-blue-700">Total Tokens</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">{{ $stats['used'] }}</div>
                <div class="text-sm text-green-700">Used Tokens</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['unused'] }}</div>
                <div class="text-sm text-orange-700">Unused Tokens</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600">{{ $stats['usage_rate'] }}%</div>
                <div class="text-sm text-purple-700">Usage Rate</div>
            </div>
        </div>

        <!-- By Election Stats -->
        @if($stats['by_election']->count() > 0)
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Tokens by Election</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($stats['by_election'] as $election)
                        <div class="bg-white rounded p-3 border">
                            <div class="font-medium text-gray-900 truncate">{{ $election['election'] }}</div>
                            <div class="text-sm text-gray-600">
                                Total: {{ $election['total'] }} | 
                                Used: <span class="text-green-600">{{ $election['used'] }}</span> | 
                                Unused: <span class="text-orange-600">{{ $election['unused'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Election</label>
                <select wire:model.live="selectedElection" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All Elections</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->id }}">{{ $election->title }}</option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="all">All Tokens</option>
                    <option value="used">Used Only</option>
                    <option value="unused">Unused Only</option>
                </select>
            </div>
            
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input wire:model.live="search" type="text" placeholder="Search by voter name, email, or election" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg">
            </div>
        </div>
    </div>

    <!-- Tokens Table -->
    <div class="bg-white rounded-lg border overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">Token Details</h3>
        </div>

        @if($tokens->count() > 0)
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Voter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Election</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tokens as $token)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                    #{{ $token->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $token->first_name ?? 'Unknown' }} {{ $token->last_name ?? '' }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ $token->email ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $token->election_title ?? 'Unknown Election' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($token->is_used)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Used
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">
                                            Unused
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ is_string($token->created_at) ? \Carbon\Carbon::parse($token->created_at)->format('M j, Y H:i') : $token->created_at->format('M j, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $token->used_at ? (is_string($token->used_at) ? \Carbon\Carbon::parse($token->used_at)->format('M j, Y H:i') : $token->used_at->format('M j, Y H:i')) : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4 p-4">
                @foreach($tokens as $token)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $token->first_name ?? 'Unknown' }} {{ $token->last_name ?? '' }}
                            </div>
                            @if($token->is_used)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Used</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-800">Unused</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500 mb-1">{{ $token->email ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-900 mb-2">{{ $token->election_title ?? 'Unknown Election' }}</div>
                        <div class="text-xs text-gray-500">
                            Created: {{ is_string($token->created_at) ? \Carbon\Carbon::parse($token->created_at)->format('M j, Y H:i') : $token->created_at->format('M j, Y H:i') }}
                            @if($token->used_at)
                                | Used: {{ is_string($token->used_at) ? \Carbon\Carbon::parse($token->used_at)->format('M j, Y H:i') : $token->used_at->format('M j, Y H:i') }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $tokens->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No tokens found</h3>
                <p class="mt-1 text-sm text-gray-500">No vote tokens match your current filters.</p>
            </div>
        @endif
    </div>
</div>