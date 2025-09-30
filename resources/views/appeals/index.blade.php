@extends('layouts.voter')

@section('title', 'My Appeals')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">My Appeals</h1>
                <p class="text-gray-600 mt-2">Track the status of your election appeals</p>
            </div>
            <a href="{{ route('voter.appeals.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Submit New Appeal
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full border-gray-300 rounded-md">
                    <option value="">All Status</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                </select>
            </div>

            <div class="flex-1 min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border-gray-300 rounded-md">
                    <option value="">All Types</option>
                    <option value="result_irregularity" {{ request('type') === 'result_irregularity' ? 'selected' : '' }}>Result Irregularity</option>
                    <option value="procedural_error" {{ request('type') === 'procedural_error' ? 'selected' : '' }}>Procedural Error</option>
                    <option value="technical_issue" {{ request('type') === 'technical_issue' ? 'selected' : '' }}>Technical Issue</option>
                    <option value="voter_fraud" {{ request('type') === 'voter_fraud' ? 'selected' : '' }}>Voter Fraud</option>
                    <option value="system_error" {{ request('type') === 'system_error' ? 'selected' : '' }}>System Error</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Appeals List -->
    <div class="space-y-6">
        @forelse($appeals as $appeal)
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $appeal->title }}</h3>
                        <p class="text-sm text-gray-600">{{ $appeal->election->title }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($appeal->status->value === 'submitted') bg-blue-100 text-blue-800
                            @elseif($appeal->status->value === 'under_review') bg-yellow-100 text-yellow-800
                            @elseif($appeal->status->value === 'approved') bg-green-100 text-green-800
                            @elseif($appeal->status->value === 'rejected') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $appeal->status->label() }}
                        </span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            @if($appeal->priority->value === 'critical') bg-red-100 text-red-800
                            @elseif($appeal->priority->value === 'high') bg-orange-100 text-orange-800
                            @elseif($appeal->priority->value === 'medium') bg-blue-100 text-blue-800
                            @else bg-gray-100 text-gray-800 @endif">
                            {{ $appeal->priority->label() }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">Type:</span>
                        <span class="text-sm text-gray-900 ml-1">{{ $appeal->type->label() }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Submitted:</span>
                        <span class="text-sm text-gray-900 ml-1">{{ $appeal->submitted_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500">Documents:</span>
                        <span class="text-sm text-gray-900 ml-1">{{ $appeal->documents->count() }}</span>
                    </div>
                </div>

                @if($appeal->review_notes)
                    <div class="bg-gray-50 rounded p-3 mb-4">
                        <span class="text-sm font-medium text-gray-700">Review Notes:</span>
                        <p class="text-sm text-gray-600 mt-1">{{ $appeal->review_notes }}</p>
                    </div>
                @endif

                @if($appeal->resolution)
                    <div class="bg-blue-50 rounded p-3 mb-4">
                        <span class="text-sm font-medium text-blue-700">Resolution:</span>
                        <p class="text-sm text-blue-600 mt-1">{{ $appeal->resolution }}</p>
                    </div>
                @endif

                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        @if($appeal->assignedTo)
                            Assigned to: {{ $appeal->assignedTo->name }}
                        @else
                            Not yet assigned
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('voter.appeals.show', $appeal) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View Details
                        </a>
                        @if($appeal->status->value === 'submitted')
                            <a href="{{ route('voter.appeals.edit', $appeal) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Edit
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No appeals yet</h3>
                <p class="text-gray-500 mb-4">You haven't submitted any appeals. If you believe there was an issue with an election, you can submit an appeal.</p>
                <a href="{{ route('voter.appeals.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Submit Your First Appeal
                </a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($appeals->hasPages())
        <div class="mt-8">
            {{ $appeals->links() }}
        </div>
    @endif
</div>
@endsection