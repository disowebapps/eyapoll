<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Voting History</h3>
    
    @if($voteRecords->count() > 0)
        <div class="space-y-4">
            @foreach($voteRecords->take(5) as $record)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $record->election->title ?? 'Election' }}</p>
                        <p class="text-sm text-gray-500">Voted on {{ $record->cast_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <div class="flex flex-col items-end space-y-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $record->election->getStatusBadgeClass() }}">
                                {{ $record->election->getStatusLabel() }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $record->election->type->color() }}-100 text-{{ $record->election->type->color() }}-800">
                                <x-heroicon-o-{{ $record->election->type->icon() }} class="w-3 h-3 mr-1" />
                                {{ $record->election->type->label() }}
                            </span>
                            <a href="{{ route('voter.receipt', $record->election) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                View Receipt
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($voteRecords->count() > 5)
            <div class="mt-4 text-center">
                <a href="{{ route('voter.history') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    View All History
                </a>
            </div>
        @endif
    @else
        <div class="text-center py-8">
            <p class="text-gray-500">No voting history yet</p>
            <p class="text-sm text-gray-400 mt-1">Your completed votes will appear here</p>
        </div>
    @endif
</div>