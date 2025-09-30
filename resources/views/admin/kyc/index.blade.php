@extends('layouts.admin')

@section('title', 'Review KYC Applications')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-8 text-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold">Review KYC Applications</h1>
                            <p class="mt-2 text-blue-100 text-lg">Verify and approve user identification documents</p>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <div class="bg-white/10 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/20">
                                <div class="text-2xl font-bold text-white">{{ $documents->total() }}</div>
                                <div class="text-sm text-blue-100">Total Documents</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ $documents->where('status', 'pending')->count() }}</div>
                            <div class="text-sm text-gray-600">Pending Review</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $documents->where('status', 'approved')->count() }}</div>
                            <div class="text-sm text-gray-600">Approved</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $documents->where('status', 'rejected')->count() }}</div>
                            <div class="text-sm text-gray-600">Rejected</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-end gap-6">
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search Users
                    </label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name, email, or document type..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>

                <div class="lg:w-64">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filter by Status
                    </label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-white">
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Documents</option>
                        <option value="pending" {{ request('status', 'pending') === 'pending' ? 'selected' : '' }}>⏳ Pending Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✅ Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                    </select>
                </div>

                <div class="lg:w-auto">
                    <button type="submit" class="w-full lg:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-sm">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Documents Display -->
        @if($documents->count() > 0)
            <!-- Mobile: Card Layout -->
            <div class="block lg:hidden">
                <div class="grid grid-cols-1 gap-6 mb-8">
                    @foreach($documents as $document)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-lg transition-shadow duration-200 overflow-hidden">
                            <!-- Card Header -->
                            <div class="px-6 py-4 border-b border-gray-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                        {{ substr($document->user->first_name, 0, 1) }}{{ substr($document->user->last_name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-900 text-lg truncate">{{ $document->user->first_name }} {{ $document->user->last_name }}</h3>
                                        <p class="text-gray-600 text-sm">{{ $document->user->email }}</p>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                @if($document->status === 'approved') bg-green-100 text-green-800 border border-green-200
                                                @elseif($document->status === 'rejected') bg-red-100 text-red-800 border border-red-200
                                                @else bg-yellow-100 text-yellow-800 border border-yellow-200 @endif">
                                                @if($document->status === 'approved')
                                                    <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @elseif($document->status === 'rejected')
                                                    <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Content -->
                            <div class="px-6 py-4">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-600">Document Type</span>
                                        <span class="text-sm font-semibold text-gray-900">
                                            @if($document->document_type instanceof \App\Enums\Auth\DocumentType)
                                                {{ $document->document_type->label() }}
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                            @endif
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-600">Uploaded</span>
                                        <span class="text-sm text-gray-900">{{ $document->created_at->format('M j, Y') }}</span>
                                    </div>

                                    @if($document->reviewed_at)
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-600">Reviewed</span>
                                            <span class="text-sm text-gray-900">{{ $document->reviewed_at->format('M j, Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Actions -->
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $document->id }}
                                    </div>
                                    <a href="{{ route('admin.kyc.review', $document) }}"
                                       onclick="logButtonClick('{{ $document->status === 'pending' ? 'Review Document' : 'View Details' }}', {{ $document->id }}, '{{ route('admin.kyc.review', $document) }}')"
                                       class="inline-flex items-center px-4 py-2 rounded-lg font-semibold text-sm transition-colors
                                       @if($document->status === 'pending')
                                           bg-blue-600 hover:bg-blue-700 text-white focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                                       @elseif($document->status === 'approved')
                                           bg-green-600 hover:bg-green-700 text-white focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                                       @else
                                           bg-gray-600 hover:bg-gray-700 text-white focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                                       @endif">
                                        @if($document->status === 'pending')
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Review Document
                                        @else
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            View Details
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Desktop: Table Layout -->
            <div class="hidden lg:block">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Document Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($documents as $document)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                                                        <span class="text-sm font-medium text-white">
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
                                            <span class="text-sm text-gray-900">
                                                @if($document->document_type instanceof \App\Enums\Auth\DocumentType)
                                                    {{ $document->document_type->label() }}
                                                @else
                                                    {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($document->status === 'approved') bg-green-100 text-green-800
                                                @elseif($document->status === 'rejected') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                @if($document->status === 'approved')
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                @elseif($document->status === 'rejected')
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                                @endif
                                                {{ ucfirst($document->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $document->created_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($document->reviewed_at)
                                                {{ $document->reviewed_at->format('M j, Y') }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.kyc.review', $document) }}"
                                               onclick="logButtonClick('{{ $document->status === 'pending' ? 'Review Document' : 'View Details' }}', {{ $document->id }}, '{{ route('admin.kyc.review', $document) }}')"
                                               class="inline-flex items-center px-3 py-1 rounded-md font-medium text-sm transition-colors
                                               @if($document->status === 'pending')
                                                   bg-blue-600 hover:bg-blue-700 text-white
                                               @elseif($document->status === 'approved')
                                                   bg-green-600 hover:bg-green-700 text-white
                                               @else
                                                   bg-gray-600 hover:bg-gray-700 text-white
                                               @endif">
                                                @if($document->status === 'pending')
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                    Review
                                                @else
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    View
                                                @endif
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if($documents->hasPages())
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ $documents->firstItem() }}</span> to <span class="font-medium">{{ $documents->lastItem() }}</span> of <span class="font-medium">{{ $documents->total() }}</span> documents
                        </div>
                        <div class="flex space-x-2">
                            {{ $documents->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="mx-auto w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">
                    @if(request('search') || request('status', 'pending') !== 'all')
                        No documents match your filters
                    @else
                        No KYC documents yet
                    @endif
                </h3>
                <p class="text-gray-600 mb-6 max-w-md mx-auto">
                    @if(request('search') || request('status', 'pending') !== 'all')
                        Try adjusting your search criteria or filters to find the documents you're looking for.
                    @else
                        When users upload their identification documents for verification, they will appear here for review.
                    @endif
                </p>
                @if(request('search') || request('status', 'pending') !== 'all')
                    <a href="{{ route('admin.kyc.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Clear Filters
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
function logButtonClick(buttonText, documentId, url) {
    console.log('KYC Button Click:', {
        button: buttonText,
        documentId: documentId,
        url: url,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent
    });

    // Send to server for logging
    fetch('/admin/kyc/log-click', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            button: buttonText,
            document_id: documentId,
            url: url,
            timestamp: new Date().toISOString()
        })
    }).catch(error => {
        console.error('Failed to log button click:', error);
    });

    // Allow the navigation to proceed
    return true;
}
</script>
@endsection