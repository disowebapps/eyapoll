<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Observer Alerts</h1>
        <div class="flex gap-4">
            <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="investigating">Investigating</option>
                <option value="resolved">Resolved</option>
                <option value="dismissed">Dismissed</option>
            </select>
            <select wire:model.live="severityFilter" class="rounded-md border-gray-300 shadow-sm">
                <option value="">All Severity</option>
                <option value="critical">Critical</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
        </div>
    </div>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        @forelse($alerts as $alert)
            <div class="border-b border-gray-200 p-4">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($alert->severity === 'critical') bg-red-100 text-red-800
                                @elseif($alert->severity === 'high') bg-orange-100 text-orange-800
                                @elseif($alert->severity === 'medium') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst($alert->severity) }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($alert->type) }}
                            </span>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($alert->status === 'active') bg-green-100 text-green-800
                                @elseif($alert->status === 'investigating') bg-blue-100 text-blue-800
                                @elseif($alert->status === 'resolved') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst($alert->status) }}
                            </span>
                        </div>
                        <h3 class="font-semibold text-gray-900">{{ $alert->title }}</h3>
                        <p class="text-gray-600 text-sm mt-1">{{ $alert->description }}</p>
                        <div class="text-xs text-gray-500 mt-2">
                            By {{ $alert->observer->full_name }} • {{ $alert->occurred_at->format('M j, Y H:i') }}
                            @if($alert->election)
                                • Election: {{ $alert->election->title }}
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        @if($alert->status === 'active')
                            <button wire:click="updateStatus({{ $alert->id }}, 'investigating')" 
                                class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                Investigate
                            </button>
                        @endif
                        @if($alert->status !== 'resolved')
                            <button wire:click="updateStatus({{ $alert->id }}, 'resolved')" 
                                class="px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                Resolve
                            </button>
                        @endif
                        @if($alert->status !== 'dismissed')
                            <button wire:click="updateStatus({{ $alert->id }}, 'dismissed')" 
                                class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700">
                                Dismiss
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-gray-500">
                No alerts found.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $alerts->links() }}
    </div>
</div>