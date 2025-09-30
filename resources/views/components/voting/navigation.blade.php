<footer class="flex items-center justify-between">
    @if($currentIndex > 0)
    <button
        wire:click="previousPosition"
        class="px-6 py-3 text-gray-600 hover:text-gray-900 font-medium transition-colors duration-200"
    >
        ← Previous
    </button>
    @else
    <div></div>
    @endif

    @if($currentIndex < $totalPositions - 1)
    <button
        wire:click="nextPosition"
        class="px-8 py-3 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition-colors duration-200"
    >
        Continue →
    </button>
    @else
    <button
        wire:click="showConfirmationModal"
        class="px-8 py-3 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition-colors duration-200"
        {{ !$canSubmit ? 'disabled' : '' }}
    >
        Review & Submit
    </button>
    @endif
</footer>