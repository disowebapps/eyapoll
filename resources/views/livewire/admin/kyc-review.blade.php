<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">KYC Document</h1>
                <p class="text-gray-600 mt-1">Review and approve user identification documents</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    <span class="font-medium">{{ $documents->total() }}</span> documents
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by user name or email..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="sm:w-48">
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $document)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ substr($document->user->first_name, 0, 1) }}{{ substr($document->user->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $document->user->first_name }} {{ $document->user->last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $document->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900">{{ $this->getDocumentTypeLabel($document->document_type) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($document->status === 'approved') bg-green-100 text-green-800
                                    @elseif($document->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($document->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $document->created_at->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button wire:click="viewDocument({{ $document->id }})"
                                        class="text-blue-600 hover:text-blue-900 mr-3">
                                    Review
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No documents found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($search || $statusFilter !== 'all')
                                        No documents match your current filters.
                                    @else
                                        No documents have been uploaded yet.
                                    @endif
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

    <!-- Review Modal -->
    @if($showReviewModal && $selectedDocument)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Review Document</h3>
                        <button wire:click="closeReviewModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Document Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">User</label>
                                <p class="text-sm text-gray-900">{{ $selectedDocument['user_name'] }}</p>
                                <p class="text-sm text-gray-500">{{ $selectedDocument['user_email'] }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Document Type</label>
                                <p class="text-sm text-gray-900">{{ $this->getDocumentTypeLabel($selectedDocument['document_type']) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($selectedDocument['status'] === 'approved') bg-green-100 text-green-800
                                    @elseif($selectedDocument['status'] === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($selectedDocument['status']) }}
                                </span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Uploaded</label>
                                <p class="text-sm text-gray-900">{{ $selectedDocument['uploaded_at']->format('M j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Document Preview -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Document Preview</label>
                        <div class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                            @php
                                try {
                                    $decryptedPath = decrypt($selectedDocument['file_path']);
                                } catch (Exception $e) {
                                    // File path might not be encrypted
                                    $decryptedPath = $selectedDocument['file_path'];
                                }
                                $extension = strtolower(pathinfo($decryptedPath, PATHINFO_EXTENSION));
                                $fileUrl = route('admin.document.view', $selectedDocument['id']);
                            @endphp
                            @if($extension === 'pdf')
                                <iframe src="{{ $fileUrl }}" class="w-full h-96 border-0"></iframe>
                            @else
                                <img src="{{ $fileUrl }}" alt="Document" class="max-w-full h-auto rounded">
                            @endif
                        </div>
                    </div>

                    <!-- Rejection Reason (if rejected) -->
                    @if($selectedDocument['status'] === 'rejected' && $selectedDocument['rejection_reason'])
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <label class="block text-sm font-medium text-red-800 mb-2">Rejection Reason</label>
                            <p class="text-sm text-red-700">{{ $selectedDocument['rejection_reason'] }}</p>
                        </div>
                    @endif

                    <!-- Review Actions -->
                    @if($selectedDocument['status'] === 'pending')
                        <div class="flex justify-end space-x-3">
                            <button wire:click="rejectDocument"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 disabled:opacity-50">
                                <span wire:loading.remove>Reject</span>
                                <span wire:loading>Processing...</span>
                            </button>
                            <button wire:click="approveDocument"
                                    wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg disabled:opacity-50">
                                <span wire:loading.remove>Approve</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        </div>

                        <!-- Rejection Reason Input -->
                        <div class="mt-4" wire:ignore>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason (required for rejection)</label>
                            <textarea wire:model="rejectionReason"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                      rows="3"
                                      placeholder="Please provide a reason for rejection..."></textarea>
                            @error('rejectionReason') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="flex justify-end">
                            <button wire:click="closeReviewModal"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Close
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>