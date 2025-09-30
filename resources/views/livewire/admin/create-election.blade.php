<div class="max-w-4xl mx-auto p-6">
    <div class="bg-white rounded-lg shadow-sm border p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Election</h1>

        <form wire:submit="create" class="space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Election Title</label>
                    <input type="text" wire:model="title" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="e.g., Student Council Election 2024">
                    @error('title') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea wire:model="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Describe the purpose and scope of this election"></textarea>
                    @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Election Type</label>
                    <select wire:model="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="general">General Election</option>
                        <option value="bye">Bye-Election</option>
                        <option value="constitutional">Constitutional Amendment</option>
                        <option value="opinion">Opinion Poll</option>
                    </select>
                    @error('type') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Candidate Application Timeline -->
            <div class="border-t pt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Candidate Application Timeline</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Applications Open</label>
                        <input type="datetime-local" wire:model="candidate_register_starts"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('candidate_register_starts') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Applications Close</label>
                        <input type="datetime-local" wire:model="candidate_register_ends"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('candidate_register_ends') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Election Timeline -->
            <div class="border-t pt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Election Timeline</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Voting Starts</label>
                        <input type="datetime-local" wire:model="starts_at"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('starts_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Voting Ends</label>
                        <input type="datetime-local" wire:model="ends_at"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('ends_at') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Timeline Preview -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-medium text-blue-900 mb-2">Timeline Preview</h3>
                <div class="text-sm text-blue-800 space-y-1">
                    <p>📝 Candidate applications: {{ $candidate_register_starts ? \Carbon\Carbon::parse($candidate_register_starts)->format('M d, Y H:i') : 'Not set' }} → {{ $candidate_register_ends ? \Carbon\Carbon::parse($candidate_register_ends)->format('M d, Y H:i') : 'Not set' }}</p>
                    <p>🗳️ Voting period: {{ $starts_at ? \Carbon\Carbon::parse($starts_at)->format('M d, Y H:i') : 'Not set' }} → {{ $ends_at ? \Carbon\Carbon::parse($ends_at)->format('M d, Y H:i') : 'Not set' }}</p>
                    <p class="text-xs italic">Note: Voter registration is continuous and only pauses when voter register is published</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t">
                <a href="{{ route('admin.elections.index') }}" 
                   class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium">
                    Create Election
                </button>
            </div>
        </form>
    </div>
</div>