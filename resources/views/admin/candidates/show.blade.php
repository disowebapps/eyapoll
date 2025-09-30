@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $candidate->getDisplayName() }}</h1>
                <p class="text-gray-600">Candidate Details</p>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('admin.candidates.edit', $candidate) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-center">
                    Edit Candidate
                </a>
                <a href="{{ route('admin.candidates.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-center">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="mb-6">
        <span class="px-3 py-1 text-sm font-medium rounded-full 
            {{ $candidate->status->value === 'approved' ? 'bg-green-100 text-green-800' : 
               ($candidate->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
               ($candidate->status->value === 'suspended' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800')) }}">
            {{ ucfirst($candidate->status->value) }}
        </span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Personal Information</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name</label>
                        <p class="text-gray-900 break-words">{{ $candidate->getDisplayName() ?: 'Name not available' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email</label>
                        <p class="text-gray-900 break-all">
                            @if($candidate->user?->email)
                                {{ Str::mask($candidate->user->email, '*', 3, -10) }}
                            @else
                                <span class="text-gray-400">Email not available</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone</label>
                        <p class="text-gray-900 break-words">
                            @if($candidate->user?->phone_number)
                                {{ Str::mask($candidate->user->phone_number, '*', 3, -4) }}
                            @else
                                <span class="text-gray-400">Not provided</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Registration Date</label>
                        <p class="text-gray-900">
                            @if($candidate->user?->created_at)
                                {{ $candidate->user->created_at->format('M j, Y') }}
                            @else
                                <span class="text-gray-400">Date not available</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Election & Position -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Election Details</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Election</label>
                        <p class="text-gray-900">{{ $candidate->election?->title ?: 'Election not found' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Position</label>
                        <p class="text-gray-900">{{ $candidate->position?->title ?: 'Position not found' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Application Fee</label>
                        <p class="text-gray-900">
                            @if(is_numeric($candidate->application_fee))
                                ${{ number_format($candidate->application_fee, 2) }}
                            @else
                                <span class="text-gray-400">Fee not set</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Payment Status</label>
                        @if($candidate->payment_status)
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $candidate->payment_status->value === 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($candidate->payment_status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                {{ ucfirst($candidate->payment_status->value) }}
                            </span>
                        @else
                            <span class="text-gray-400">Status unknown</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Manifesto -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Manifesto</h2>
                <div class="prose max-w-none">
                    <p class="text-gray-700">{{ e($candidate->manifesto) ?: 'No manifesto provided' }}</p>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Documents</h2>
                @if($candidate->documents->count() > 0)
                    <div class="space-y-3">
                        @foreach($candidate->documents as $document)
                        <div class="flex items-center justify-between p-3 border rounded-lg">
                            <div>
                                <p class="font-medium">{{ e($document->getDocumentTypeLabel()) }}</p>
                                <p class="text-sm text-gray-500">Uploaded {{ $document->created_at->format('M j, Y') }}</p>
                                @if($document->reviewer)
                                    <p class="text-xs text-gray-400">Reviewed by {{ e($document->reviewer->first_name) }}</p>
                                @endif
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $document->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($document->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($document->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">No documents uploaded</p>
                @endif
            </div>
        </div>

        <!-- Right Column - Stats & Actions -->
        <div class="space-y-6">
            <!-- Application Progress -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Application Progress</h2>
                @if(isset($applicationProgress['steps']) && is_array($applicationProgress['steps']))
                    <div class="space-y-3">
                        @foreach(['basic_info' => 'Basic Info', 'payment' => 'Payment', 'documents' => 'Documents', 'approval' => 'Approval'] as $key => $label)
                            <div class="flex items-center justify-between">
                                <span class="text-sm">{{ $label }}</span>
                                <span class="text-{{ ($applicationProgress['steps'][$key] ?? false) ? 'green' : 'gray' }}-500">
                                    {{ ($applicationProgress['steps'][$key] ?? false) ? '✓' : '○' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        <div class="bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $applicationProgress['percentage'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $applicationProgress['percentage'] ?? 0 }}% Complete</p>
                    </div>
                @else
                    <p class="text-gray-400">Progress data unavailable</p>
                @endif
            </div>

            <!-- Vote Statistics -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Vote Statistics</h2>
                @if(is_array($voteStats))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Votes Received</span>
                            <span class="text-2xl font-bold text-indigo-600">{{ $voteStats['vote_count'] ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Vote Percentage</span>
                            <span class="text-lg font-semibold">{{ number_format($voteStats['vote_percentage'] ?? 0, 1) }}%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Current Ranking</span>
                            <span class="text-lg font-semibold">#{{ $voteStats['ranking'] ?? 1 }}</span>
                        </div>
                        @if($voteStats['is_winner'] ?? false)
                        <div class="mt-3 p-2 bg-green-100 rounded-lg text-center">
                            <span class="text-green-800 font-semibold">🏆 Winner</span>
                        </div>
                        @endif
                    </div>
                @else
                    <p class="text-gray-400">Vote statistics unavailable</p>
                @endif
            </div>

            <!-- Admin Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Admin Actions</h2>
                <div class="space-y-3">
                    @if($candidate->status->value === 'pending')
                        <form method="POST" action="{{ route('admin.candidates.approve', $candidate) }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Approve Candidate
                            </button>
                        </form>
                    @elseif($candidate->status->value === 'suspended')
                        @livewire('admin.candidate-action-modal', ['candidate' => $candidate, 'action' => 'unsuspend'])
                    @endif
                </div>
            </div>

            <!-- Suspension Info -->
            @if($candidate->status->value === 'suspended' && $candidate->suspender)
            <div class="bg-orange-50 border border-orange-200 rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 text-orange-800">Suspension Details</h2>
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-orange-600">Suspended by:</span>
                        <p class="font-medium text-orange-900">{{ $candidate->suspender->first_name }} {{ $candidate->suspender->last_name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-orange-600">Suspended on:</span>
                        <p class="font-medium text-orange-900">{{ $candidate->suspended_at->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-orange-600">Reason:</span>
                        <p class="font-medium text-orange-900">{{ $candidate->suspension_reason }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-orange-600">Duration:</span>
                        <p class="font-medium text-orange-900">{{ $candidate->suspended_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Action History -->
            @if($candidate->actionHistory->count() > 0)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Action History</h2>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($candidate->actionHistory as $history)
                    <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    {{ $history->action === 'suspended' ? 'bg-orange-100 text-orange-800' : 
                                       ($history->action === 'unsuspended' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800') }}">
                                    {{ ucfirst($history->action) }}
                                </span>
                                <span class="text-sm text-gray-600">by {{ $history->admin->first_name }} {{ $history->admin->last_name }}</span>
                            </div>
                            @if($history->reason)
                            <p class="text-sm text-gray-700 mt-1 break-words">{{ $history->reason }}</p>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 ml-4 whitespace-nowrap">
                            {{ $history->created_at->format('M j, Y g:i A') }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Payment Review Component -->
            @livewire('admin.payment-review', ['candidate' => $candidate])

            <!-- Approval Info -->
            @if($candidate->approver)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4">Approval Details</h2>
                <div class="space-y-2">
                    <div>
                        <span class="text-sm text-gray-600">Approved by:</span>
                        <p class="font-medium">{{ $candidate->approver->first_name }} {{ $candidate->approver->last_name }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Approved on:</span>
                        <p class="font-medium">{{ $candidate->approved_at->format('M j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 z-50 hidden transform transition-all duration-300 translate-x-full">
    <div id="toastContent" class="px-6 py-4 rounded-lg shadow-lg text-white max-w-sm">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <span id="toastIcon" class="mr-3 font-bold"></span>
                <span id="toastMessage"></span>
            </div>
            <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200">
                ×
            </button>
        </div>
    </div>
</div>



<script>
// Toast functions
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const content = document.getElementById('toastContent');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');
    
    content.className = `px-6 py-4 rounded-lg shadow-lg text-white max-w-sm ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    }`;
    icon.textContent = type === 'success' ? '✓' : '✕';
    messageEl.textContent = message;
    
    toast.classList.remove('hidden', 'translate-x-full');
    toast.classList.add('translate-x-0');
    
    setTimeout(hideToast, 4000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.remove('translate-x-0');
    toast.classList.add('translate-x-full');
    setTimeout(() => toast.classList.add('hidden'), 300);
}

// Show flash messages
@if(session('success'))
    setTimeout(() => showToast('{{ session('success') }}', 'success'), 100);
@endif

@if(session('error'))
    setTimeout(() => showToast('{{ session('error') }}', 'error'), 100);
@endif
</script>
@endsection