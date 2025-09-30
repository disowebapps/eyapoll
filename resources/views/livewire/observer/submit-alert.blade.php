<div class="min-h-screen bg-gray-50 py-8" x-data="alertForm()">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-red-100 rounded-lg mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Submit Observer Alert</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">Report security concerns, voting irregularities, or technical issues to maintain election integrity and transparency.</p>
        </div>

        <!-- Success Message -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <p class="text-green-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Alert Information</h2>
                <p class="text-sm text-gray-600 mt-1">Please provide detailed information about the issue</p>
            </div>

            <form class="p-6 space-y-6">
            <!-- Alert Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Alert Type</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach([['security', 'Security', 'Security concerns'], ['irregularity', 'Irregularity', 'Voting issues'], ['technical', 'Technical', 'System problems'], ['audit', 'Audit', 'Record issues'], ['other', 'Other', 'General concerns']] as [$value, $label, $desc])
                        <div class="relative">
                            <input type="radio" 
                                   x-model="type" 
                                   value="{{ $value }}" 
                                   id="type_{{ $value }}" 
                                   class="sr-only peer"
                                   wire:model.live="type">
                            <label for="type_{{ $value }}" 
                                   class="flex flex-col p-4 border-2 rounded-lg cursor-pointer transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 border-gray-200">
                                <span class="font-medium text-gray-900">{{ $label }}</span>
                                <span class="text-sm text-gray-500 mt-1">{{ $desc }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('type') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Severity Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Severity Level</label>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach([['low', 'Low', 'bg-gray-100 text-gray-800'], ['medium', 'Medium', 'bg-yellow-100 text-yellow-800'], ['high', 'High', 'bg-orange-100 text-orange-800'], ['critical', 'Critical', 'bg-red-100 text-red-800']] as [$value, $label, $classes])
                        <div class="relative">
                            <input type="radio" 
                                   x-model="severity" 
                                   value="{{ $value }}" 
                                   id="severity_{{ $value }}" 
                                   class="sr-only peer"
                                   wire:model.live="severity">
                            <label for="severity_{{ $value }}" 
                                   class="flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer transition-all peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 border-gray-200">
                                <span class="px-3 py-1 rounded-full text-xs font-medium mb-2 {{ $classes }}">{{ $label }}</span>
                                <span class="text-sm text-gray-600 text-center">{{ ucfirst($value) }} priority</span>
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('severity') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Election Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Related Election <span class="text-gray-400">(Optional)</span></label>
                <select wire:model.live="election_id" 
                        x-model="election_id"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Select an election if applicable</option>
                    @foreach($elections as $election)
                        <option value="{{ $election->id }}">{{ $election->title }} - {{ ucfirst($election->status->value ?? $election->status) }}</option>
                    @endforeach
                </select>
                @error('election_id') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Title & Description -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Title</label>
                    <input type="text" 
                           wire:model.live="title" 
                           x-model="title"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" 
                           placeholder="Brief description of the issue">
                    @error('title') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Occurrence Time</label>
                    <input type="datetime-local" 
                           wire:model.live="occurred_at" 
                           x-model="occurred_at"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                    @error('occurred_at') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Detailed Description</label>
                <textarea wire:model.live="description" 
                          x-model="description"
                          rows="4" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" 
                          placeholder="Provide a detailed description of what you observed, including what happened, when, who was involved, and any evidence."></textarea>
                <div class="mt-1 text-sm text-gray-500">Minimum 10 characters required</div>
                @error('description') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Submit Section -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Secure submission to administrators
                </div>
                <button type="button" 
                        wire:click="submit" 
                        :disabled="!canSubmit()"
                        :class="canSubmit() ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'"
                        class="px-6 py-2 text-white rounded-md font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <span x-show="!submitting">Submit Alert</span>
                    <span x-show="submitting" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<script>
function alertForm() {
    return {
        type: @entangle('type'),
        severity: @entangle('severity'),
        title: @entangle('title'),
        description: @entangle('description'),
        election_id: @entangle('election_id'),
        occurred_at: @entangle('occurred_at'),
        submitting: false,
        
        canSubmit() {
            return this.type && this.severity && this.title && this.description && this.occurred_at && !this.submitting;
        }
    }
}
</script>