<div
    wire:click="{{ $canSelect ? 'toggleCandidate(' . $positionId . ', ' . $candidate['id'] . ')' : '' }}"
    class="group relative border border-gray-200 rounded-lg p-8 transition-all duration-200 {{ $isSelected ? 'border-gray-900 bg-gray-50' : ($canSelect ? 'hover:border-gray-400 cursor-pointer' : 'opacity-40 cursor-not-allowed') }}"
>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <h3 class="text-xl font-medium text-gray-900 mb-3">{{ $candidate['name'] }}</h3>
            @if($candidate['manifesto'])
            <p class="text-gray-600 leading-relaxed">{{ Str::limit($candidate['manifesto'], 300) }}</p>
            @endif
        </div>
        <div class="ml-6 flex-shrink-0">
            <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center {{ $isSelected ? 'border-gray-900 bg-gray-900' : 'border-gray-300' }}">
                @if($isSelected)
                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                @endif
            </div>
        </div>
    </div>
</div>