<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Candidate Approvals</h1>
            <p class="text-gray-600 mt-1">Review and approve candidate applications</p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Filters & Search</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        id="search"
                        class="form-input"
                        placeholder="Name, email, manifesto..."
                    >
                </div>

                <!-- Election Filter -->
                <div>
                    <label for="electionFilter" class="block text-sm font-medium text-gray-700">Election</label>
                    <select wire:model.live="electionFilter" id="electionFilter" class="form-select">
                        <option value="">All Elections</option>
                        @foreach($elections as $election)
                        <option value="{{ $election->id }}">{{ $election->title }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model.live="statusFilter" id="statusFilter" class="form-select">
                        @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" {{ $statusFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Per Page -->
                <div>
                    <label for="perPage" class="block text-sm font-medium text-gray-700">Per Page</label>
                    <select wire:model.live="perPage" id="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <!-- Clear Filters -->
            @if($search || $electionFilter || $statusFilter !== 'pending')
            <div class="mt-4">
                <button
                    wire:click="$set('search', ''); $set('electionFilter', ''); $set('statusFilter', 'pending')"
                    class="text-sm text-gray-600 hover:text-gray-800 underline"
                >
                    Clear all filters
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Candidates Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Candidates</h3>
                <span class="text-sm text-gray-500">{{ $candidates->total() }} total candidates</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-header">Candidate</th>
                        <th class="table-header">Election & Position</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Documents</th>
                        <th
                            wire:click="sortBy('created_at')"
                            class="table-header cursor-pointer hover:bg-gray-100"
                        >
                            <div class="flex items-center">
                                Applied
                                @if($sortField === 'created_at')
                                <svg class="w-4 h-4 ml-1 {{ $sortDirection === 'asc' ? 'transform rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                @endif
                            </div>
                        </th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($candidates as $candidate)
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            {{ substr($candidate->user->first_name, 0, 1) }}{{ substr($candidate->user->last_name, 0, 1) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $candidate->user->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $candidate->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="text-sm text-gray-900">{{ $candidate->election->title }}</div>
                            <div class="text-sm text-gray-500">{{ $candidate->position->title }}</div>
                        </td>
                        <td class="table-cell">
                            <span class="status-badge status-{{ $candidate->status->value }}">
                                {{ $candidate->status->label() }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center">
                                <span class="text-sm text-gray-900">{{ $candidate->documents->count() }}</span>
                                @if($candidate->documents->where('status', 'pending')->count() > 0)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $candidate->documents->where('status', 'pending')->count() }} pending
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="text-sm text-gray-900">{{ $candidate->created_at->format('M j, Y') }}</div>
                            <div class="text-sm text-gray-500">{{ $candidate->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center space-x-2">
                                <button
                                    wire:click="viewCandidate({{ $candidate->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    title="Review Application"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>

                                @if($candidate->status->value === 'pending')
                                <button
                                    wire:click="approveCandidate({{ $candidate->id }})"
                                    class="text-green-600 hover:text-green-900 text-sm font-medium"
                                    title="Approve Candidate"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>

                                <button
                                    wire:click="rejectCandidate({{ $candidate->id }})"
                                    onclick="return confirm('Are you sure you want to reject this candidate?')"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                                    title="Reject Candidate"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif

                                @if($candidate->status->value === 'approved')
                                <button
                                    wire:click="suspendCandidate({{ $candidate->id }})"
                                    onclick="return confirm('Are you sure you want to suspend this candidate?')"
                                    class="text-orange-600 hover:text-orange-900 text-sm font-medium"
                                    title="Suspend Candidate"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="table-cell text-center py-8">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No candidates found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($search || $electionFilter || $statusFilter !== 'pending')
                                    No candidates match your current filters.
                                    @else
                                    No candidates are currently pending approval.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($candidates->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $candidates->links() }}
        </div>
        @endif
    </div>

    <!-- Approval Modal -->
    @if($showApprovalModal && $selectedCandidate)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Review Candidate Application</h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Candidate Info -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Candidate Information</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm text-gray-500">Name</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->user->full_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Email</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Phone</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->user->phone ?? 'N/A' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Election</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->election->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Position</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->position->title }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-900 mb-3">Application Details</h4>
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm text-gray-500">Status</dt>
                                    <dd>
                                        <span class="status-badge status-{{ $selectedCandidate->status->value }}">
                                            {{ $selectedCandidate->status->label() }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Applied On</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Documents</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $selectedCandidate->documents->count() }} uploaded</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Manifesto -->
                @if($selectedCandidate->manifesto)
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Manifesto</h4>
                    <div class="prose prose-sm max-w-none">
                        {!! nl2br(e($selectedCandidate->manifesto)) !!}
                    </div>
                </div>
                @endif

                <!-- Documents -->
                @if($selectedCandidate->documents->count() > 0)
                <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Submitted Documents</h4>
                    <div class="space-y-3">
                        @foreach($selectedCandidate->documents as $document)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $document->document_type->label() }}</p>
                                    <p class="text-xs text-gray-500">Uploaded {{ $document->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if($document->status === 'approved')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Approved
                                </span>
                                @elseif($document->status === 'rejected')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                                @else
                                <button
                                    wire:click="approveDocument({{ $document->id }})"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium"
                                >
                                    Approve
                                </button>
                                <button
                                    wire:click="rejectDocument({{ $document->id }})"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                                >
                                    Reject
                                </button>
                                @endif
                                <a
                                    href="{{ route('document.download', $document->id) }}"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    View
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                @if($selectedCandidate->status->value === 'pending')
                <div class="flex justify-end space-x-4">
                    <button
                        wire:click="closeModal"
                        class="btn-secondary"
                    >
                        Cancel
                    </button>

                    <div class="flex space-x-2">
                        <button
                            wire:click="rejectCandidate"
                            class="btn-danger flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reject Application
                        </button>

                        <button
                            wire:click="approveCandidate"
                            class="btn-success flex items-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Approve Application
                        </button>
                    </div>
                </div>

                <!-- Approval Notes (shown when approving) -->
                <div class="mt-4" x-data="{ showNotes: false }" x-show="showNotes" x-transition>
                    <label for="approvalNotes" class="block text-sm font-medium text-gray-700 mb-2">Approval Notes (Optional)</label>
                    <textarea
                        wire:model="approvalNotes"
                        id="approvalNotes"
                        rows="3"
                        class="form-input"
                        placeholder="Add any notes about this approval..."
                    ></textarea>
                </div>

                <!-- Rejection Reason (shown when rejecting) -->
                <div class="mt-4" x-data="{ showReason: false }" x-show="showReason" x-transition>
                    <label for="rejectionReason" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason</label>
                    <textarea
                        wire:model="rejectionReason"
                        id="rejectionReason"
                        rows="3"
                        class="form-input"
                        placeholder="Please provide a reason for rejection..."
                        required
                    ></textarea>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<script>
document.addEventListener('livewire:loaded', () => {
    Livewire.on('candidateApproved', () => {
        // Close modal and show success
        @this.closeModal();
    });

    Livewire.on('candidateRejected', () => {
        // Close modal and show success
        @this.closeModal();
    });
});
</script>