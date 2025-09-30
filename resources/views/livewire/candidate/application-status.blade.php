<div>
    <!-- Application Header -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Application Status</h1>
                    <p class="text-gray-600 mt-1">{{ $application->election->title }} - {{ $application->position->title }}</p>
                    <div class="flex items-center mt-2 space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusBadgeColor }}-100 text-{{ $statusBadgeColor }}-800">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $application->status->label() }}
                        </span>
                        <span class="text-sm text-gray-500">
                            Applied on {{ $application->created_at->format('M j, Y g:i A') }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">Application ID</div>
                    <div class="font-mono text-lg font-semibold">#{{ $application->id }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Main Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Application Information -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Application Details</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Election</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $application->election->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Position</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $application->position->title }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Application Fee</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                @if($application->application_fee > 0)
                                    ${{ number_format($application->application_fee, 2) }}
                                @else
                                    Free
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Status</dt>
                            <dd class="text-sm text-gray-900 mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $application->payment_status->color() }}-100 text-{{ $application->payment_status->color() }}-800">
                                    {{ $application->payment_status->label() }}
                                </span>
                            </dd>
                        </div>
                        @if($application->approved_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Approved At</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $application->approved_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        @endif
                        @if($application->approver)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                            <dd class="text-sm text-gray-900 mt-1">{{ $application->approver->name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Manifesto -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Your Manifesto</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="prose prose-sm max-w-none">
                            {!! nl2br(e($application->manifesto)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            @if(count($documents) > 0)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Submitted Documents</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        @foreach($documents as $document)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $document['filename'] }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ ucfirst($document['type']) }} • {{ number_format($document['file_size'] / 1024, 1) }} KB •
                                        Uploaded {{ $document['uploaded_at']->format('M j, Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($document['status'] === 'approved') bg-green-100 text-green-800
                                    @elseif($document['status'] === 'rejected') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($document['status']) }}
                                </span>
                                <button
                                    wire:click="downloadDocument({{ $document['id'] }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                >
                                    Download
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Application Status</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-{{ $statusBadgeColor }}-100 rounded-lg flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-{{ $statusBadgeColor }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $application->status->label() }}</div>
                            <div class="text-sm text-gray-500">{{ $application->status->description() }}</div>
                        </div>
                    </div>

                    @if($application->status->value === 'rejected' && $application->rejection_reason)
                    <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-red-800">Rejection Reason</h4>
                                <p class="text-sm text-red-700 mt-1">{{ $application->rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payment Information -->
            @if($paymentInfo)
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Payment Information</h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Amount</span>
                            <span class="text-sm font-medium">${{ number_format($paymentInfo['amount'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Status</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-{{ $application->payment_status->color() }}-100 text-{{ $application->payment_status->color() }}-800">
                                {{ $application->payment_status->label() }}
                            </span>
                        </div>
                        @if($paymentInfo['reference'])
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Reference</span>
                            <span class="text-sm font-mono">{{ $paymentInfo['reference'] }}</span>
                        </div>
                        @endif
                    </div>

                    @if($paymentInfo['can_pay'])
                    <div class="mt-4">
                        <button
                            wire:click="processPayment"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                        >
                            Process Payment
                        </button>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                </div>
                <div class="px-6 py-4 space-y-3">
                    @if($application->canWithdraw())
                    <button
                        wire:click="withdrawApplication"
                        onclick="return confirm('Are you sure you want to withdraw this application? This action cannot be undone.')"
                        class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        Withdraw Application
                    </button>
                    @endif

                    <a
                        href="{{ route('candidate.dashboard') }}"
                        class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center block"
                    >
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>