<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Notification Templates</h2>
            <p class="text-gray-600">Manage email, SMS, and in-app notification templates</p>
        </div>
        <button wire:click="createTemplate"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Create Template
        </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="setActiveTab('email')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'email' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Email Templates
            </button>
            <button wire:click="setActiveTab('sms')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'sms' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                SMS Templates
            </button>
            <button wire:click="setActiveTab('in_app')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'in_app' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                In-App Templates
            </button>
        </nav>
    </div>

    <!-- Templates Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($templates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $template->eventType?->label() ?? $template->event_type }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-xs">
                                    @if($activeTab === 'email')
                                        <div class="font-medium">{{ Str::limit($template->subject, 40) }}</div>
                                        <div class="text-gray-500 text-xs mt-1">{{ Str::limit($template->body, 60) }}</div>
                                    @else
                                        <div>{{ Str::limit($template->message, 60) }}</div>
                                        @if($activeTab === 'sms' && $template->estimated_cost)
                                            <div class="text-gray-500 text-xs mt-1">${{ number_format($template->estimated_cost, 3) }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button wire:click="toggleTemplate({{ $template->id }})"
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $template->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="editTemplate({{ $template->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click="deleteTemplate({{ $template->id }})"
                                            wire:confirm="Are you sure you want to delete this template?"
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No {{ $activeTab }} templates found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $templateId ? 'Edit' : 'Create' }} {{ ucfirst($activeTab) }} Template
                    </h3>

                    <form wire:submit.prevent="saveTemplate" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Event Type</label>
                            <select wire:model="eventType" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Select Event Type</option>
                                @foreach($eventTypes as $event)
                                    <option value="{{ $event->value }}">{{ $event->label() }}</option>
                                @endforeach
                            </select>
                            @error('eventType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($activeTab === 'email')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Subject</label>
                                <input wire:model="subject" type="text"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                {{ $activeTab === 'email' ? 'Body' : 'Message' }}
                            </label>
                            <textarea wire:model="body" rows="6"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Use {{ variable }} for dynamic content"></textarea>
                            @error('body') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($activeTab === 'sms')
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estimated Cost ($)</label>
                                <input wire:model="estimatedCost" type="number" step="0.001"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('estimatedCost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="flex items-center">
                            <input wire:model="isActive" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" wire:click="closeModal"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $templateId ? 'Update' : 'Create' }} Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
