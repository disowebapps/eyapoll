@extends('layouts.admin')

@section('title', 'Document Not Found')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
            <!-- Error Icon -->
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-6">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>

            <!-- Error Title -->
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Document Not Found</h1>
            <p class="text-gray-600 text-center mb-6">
                The requested document file could not be located in storage.
            </p>

            <!-- Document Details -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Document Information</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Document ID:</span>
                        <span class="font-medium text-gray-900">{{ $document->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">User:</span>
                        <span class="font-medium text-gray-900">{{ $document->user->full_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Type:</span>
                        <span class="font-medium text-gray-900">{{ $document->document_type instanceof \App\Enums\Auth\DocumentType ? $document->document_type->label() : ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            @if($document->status === 'approved') bg-green-100 text-green-800
                            @elseif($document->status === 'rejected') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($document->status) }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Uploaded:</span>
                        <span class="font-medium text-gray-900">{{ $document->created_at->format('M j, Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Technical Details (Collapsible) -->
            <details class="mb-6">
                <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Technical Details
                </summary>
                <div class="mt-3 p-3 bg-gray-100 rounded-lg text-xs font-mono">
                    <div class="space-y-1">
                        <div><strong>Original Path:</strong> {{ $filePath }}</div>
                        <div><strong>Full Path:</strong> {{ $fullPath }}</div>
                        <div><strong>Storage Disk:</strong> local</div>
                    </div>
                </div>
            </details>

            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ route('admin.kyc.review', $document) }}"
                   class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Document Review
                </a>

                <a href="{{ route('admin.kyc.index') }}"
                   class="w-full flex items-center justify-center px-4 py-3 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Back to KYC Dashboard
                </a>
            </div>

            <!-- Help Text -->
            <p class="text-xs text-gray-500 text-center mt-6">
                If you believe this is an error, please contact the system administrator.
            </p>
        </div>
    </div>
</div>
@endsection