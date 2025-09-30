<div>
    <!-- Header -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Document Review</h1>
                    <p class="text-gray-600 mt-1">Review and approve candidate documents</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button
                        wire:click="bulkApprove"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                        onclick="return confirm('Are you sure you want to approve all pending documents?')"
                    >
                        Bulk Approve All
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Total Documents</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Pending Review</h3>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Approved</h3>
                            <p class="text-2xl font-bold text-green-600">{{ $stats['approved'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Rejected</h3>
                            <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Search</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Search by name or election..."
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                        wire:model.live="statusFilter"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Document Type</label>
                    <select
                        wire:model.live="documentTypeFilter"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Types</option>
                        <option value="cv">CV/Resume</option>
                        <option value="certificates">Certificates</option>
                        <option value="photo">Photo</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        wire:click="$refresh"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                        Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Election</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($documents as $document)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $document->candidate->user->getFullNameAttribute() }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $document->candidate->user->email }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $document->candidate->election->title }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $document->candidate->position->title }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($document->document_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $document->original_filename }}</div>
                            <div class="text-sm text-gray-500">{{ number_format($document->file_size / 1024, 1) }} KB</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($document->status === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending Review
                                </span>
                            @elseif($document->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($document->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $document->created_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button
                                wire:click="viewDocument({{ $document->id }})"
                                class="text-blue-600 hover:text-blue-900 mr-3"
                            >
                                Review
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $documents->links() }}
        </div>
    </div>

    <!-- Document Review Modal -->
    @if($showDocumentModal && $selectedDocument)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="document-modal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Document Review</h3>
                    <button wire:click="closeDocumentModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Document Preview -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Preview</label>
                    <div class="border border-gray-300 rounded-lg bg-gray-50 p-4">
                        @php
                            $fileUrl = $selectedDocument->getFileUrl();
                        @endphp

                        @if($selectedDocument->isPdf())
                            <iframe src="{{ $fileUrl }}" class="w-full h-96 border-0 rounded"></iframe>
                            <div class="mt-4 text-center">
                                <a href="{{ $fileUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                    Open PDF in new tab
                                </a>
                            </div>
                        @else
                            <div class="text-center">
                                <img src="{{ $fileUrl }}" alt="Document" class="max-w-full h-auto rounded mx-auto" style="max-height: 600px;">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Document Info -->
                <div class="mb-6">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Candidate</label>
                            <p class="text-sm text-gray-900">{{ $selectedDocument->candidate->user->getFullNameAttribute() }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Election</label>
                            <p class="text-sm text-gray-900">{{ $selectedDocument->candidate->election->title }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Document Type</label>
                            <p class="text-sm text-gray-900">{{ ucfirst($selectedDocument->document_type) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">File Size</label>
                            <p class="text-sm text-gray-900">{{ number_format($selectedDocument->file_size / 1024, 1) }} KB</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">File Name</label>
                        <p class="text-sm text-gray-900">{{ $selectedDocument->original_filename }}</p>
                    </div>

                    @if($selectedDocument->review_notes)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Previous Review Notes</label>
                        <p class="text-sm text-gray-900 bg-gray-50 p-2 rounded">{{ $selectedDocument->review_notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Review Actions -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Review Notes</label>
                    <textarea
                        wire:model="reviewNotes"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        rows="3"
                        placeholder="Add notes about your review decision..."
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-between">
                    <div>
                        <button
                            wire:click="downloadDocument"
                            class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors mr-2"
                        >
                            Download File
                        </button>
                    </div>
                    <div>
                        <button
                            wire:click="closeDocumentModal"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors mr-2"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="rejectDocument"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors mr-2"
                        >
                            Reject
                        </button>
                        <button
                            wire:click="approveDocument"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                        >
                            Approve
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>