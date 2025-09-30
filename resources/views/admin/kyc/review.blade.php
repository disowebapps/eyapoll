@extends('layouts.admin')

@section('title', 'Review KYC Document')

@section('content')
@php
    $fileUrl = route('admin.document.view', $document->id);
    try {
        $decryptedPath = decrypt($document->file_path);
        $extension = strtolower(pathinfo($decryptedPath, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isPdf = $extension === 'pdf';
        $fileSize = 'Unknown';
        if (\Illuminate\Support\Facades\Storage::exists($decryptedPath)) {
            $fileSizeBytes = \Illuminate\Support\Facades\Storage::size($decryptedPath);
            $fileSize = $fileSizeBytes >= 1048576
                ? round($fileSizeBytes / 1048576, 1) . ' MB'
                : round($fileSizeBytes / 1024, 1) . ' KB';
        }
    } catch (\Exception $e) {
        $decryptedPath = $document->file_path;
        $extension = strtolower(pathinfo($decryptedPath, PATHINFO_EXTENSION));
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        $isPdf = $extension === 'pdf';
        $fileSize = 'Unknown';
    }
@endphp
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.kyc.index') }}" class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-xl sm:text-2xl font-bold text-gray-900">KYC Document Review</h1>
                        <p class="text-gray-600 mt-1 text-sm sm:text-base">Review and verify user identity documents</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <!-- Document ID -->
                    <div class="text-sm text-gray-500 order-3 sm:order-1">
                        ID: <span class="font-mono font-medium">{{ $document->id }}</span>
                    </div>
                    <!-- Priority and Status -->
                    <div class="flex items-center space-x-2 order-1 sm:order-2">
                        @php
                            $daysOld = $document->created_at->diffInDays(now());
                            $priority = $daysOld > 7 ? 'high' : ($daysOld > 3 ? 'medium' : 'low');
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 sm:px-2.5 sm:py-0.5 rounded-full text-xs font-medium
                            @if($priority === 'high') bg-red-100 text-red-800
                            @elseif($priority === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            @if($priority === 'high')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">Urgent</span>
                                <span class="sm:hidden">High</span>
                            @elseif($priority === 'medium')
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">Review Soon</span>
                                <span class="sm:hidden">Med</span>
                            @else
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">Normal</span>
                                <span class="sm:hidden">Low</span>
                            @endif
                        </span>
                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-3 py-1 sm:px-4 sm:py-2 rounded-xl text-sm font-semibold
                            @if($document->status === 'approved') bg-green-100 text-green-800 border border-green-200
                            @elseif($document->status === 'rejected') bg-red-100 text-red-800 border border-red-200
                            @else bg-yellow-100 text-yellow-800 border border-yellow-200 @endif">
                            @if($document->status === 'approved')
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @elseif($document->status === 'rejected')
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            @else
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                            @endif
                            <span class="hidden sm:inline">{{ ucfirst($document->status) }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile Layout: Current stacked design -->
        <div class="block lg:hidden space-y-8">
            <!-- Document Preview -->
            <div class="space-y-6">
                <!-- Document Preview Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Document Preview</h2>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium bg-gray-100 text-gray-800">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ strtoupper($extension ?: 'FILE') }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">

                        @if($isPdf)
                            <div class="space-y-4">
                                <div class="bg-white rounded-lg border-2 border-gray-200 overflow-hidden">
                                    <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                            class="w-full h-96 border-0 rounded"
                                            loading="lazy"
                                            title="PDF Document Preview">
                                    </iframe>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">File Size:</span> {{ $fileSize }}
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                            Open in New Tab
                                        </a>
                                        <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                           class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif($isImage)
                            <div class="space-y-4">
                                <div class="bg-white rounded-lg border-2 border-gray-200 p-4">
                                    <img src="{{ $fileUrl }}"
                                         alt="Document Preview"
                                         class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
                                         style="max-height: 600px;"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDE4SDlhMSAxIDAgMCAxLTEtMVY1YTEgMSAwIDAxMS0xaDQuNTg2YTEgMSAwIDAuNzA3LjI5M2w1LjQxNCA1LjQxNGEhMSAwIDAuMjg2LjI5N1YxOWExIDEgMCAwMS0xIDF6IiBzdHJva2U9IiM5Q0E0QUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='; this.alt='Preview unavailable';">
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">File Size:</span> {{ $fileSize }}
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="zoomImage(this.previousElementSibling.querySelector('img'), 1.2)"
                                                class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                            </svg>
                                            Zoom In
                                        </button>
                                        <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                           class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded-lg transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            Download
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Document Preview Unavailable</h3>
                                <p class="text-sm text-gray-600 mb-4">This file type cannot be previewed inline. Please download to view the document.</p>
                                <div class="flex items-center justify-center space-x-2 mb-4">
                                    <span class="text-sm text-gray-500">File Size: {{ $fileSize }}</span>
                                </div>
                                <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Document Verification Checklist -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Verification Checklist</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is authentic</span>
                            </div>
                            <span class="text-xs text-gray-500">Auto-verified</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is legible</span>
                            </div>
                            <span class="text-xs text-gray-500">Visual check</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Information matches user profile</span>
                            </div>
                            <span class="text-xs text-gray-500">Cross-reference</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is not expired</span>
                            </div>
                            <span class="text-xs text-gray-500">Date validation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- User Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">User Information</h2>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                {{ substr($document->user->first_name, 0, 1) }}{{ substr($document->user->last_name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $document->user->full_name }}</h3>
                                <p class="text-gray-600">{{ $document->user->email }}</p>
                                <p class="text-xs text-gray-500 mt-1">User ID: {{ $document->user->id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Phone</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->phone_number ?? 'Not provided' }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Registration</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Last Login</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->updated_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($document->user->status->value === 'approved') bg-green-100 text-green-800
                                        @elseif($document->user->status->value === 'rejected') bg-red-100 text-red-800
                                        @elseif($document->user->status->value === 'review') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $document->user->status->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Document Details</h2>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Document ID</span>
                                <span class="text-sm font-mono font-semibold text-gray-900">{{ $document->id }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Type</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($document->document_type instanceof \App\Enums\Auth\DocumentType)
                                        {{ $document->document_type->label() }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Uploaded</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">File Size</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $fileSize }}</span>
                            </div>
                        </div>
                        @if($document->reviewed_at)
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Reviewed</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->reviewed_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        @endif
                        @if($document->status === 'rejected' && $document->rejection_reason)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Rejection Reason</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $document->rejection_reason }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Verification Checklist -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Verification Checklist</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is authentic</span>
                            </div>
                            <span class="text-xs text-gray-500">Auto-verified</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is legible</span>
                            </div>
                            <span class="text-xs text-gray-500">Visual check</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Information matches user profile</span>
                            </div>
                            <span class="text-xs text-gray-500">Cross-reference</span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is not expired</span>
                            </div>
                            <span class="text-xs text-gray-500">Date validation</span>
                        </div>
                    </div>
                </div>

                <!-- Review Actions -->
                @if($document->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Review Actions</h2>

                    <form id="reviewForm" class="space-y-6">
                        @csrf

                        <!-- Review Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Review Notes
                                <span class="text-sm font-normal text-gray-600">(optional - for internal records)</span>
                            </label>
                            <textarea name="review_notes"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      rows="3"
                                      placeholder="Add any notes about your review decision..."></textarea>
                        </div>

                        <!-- Rejection Reason (conditionally shown) -->
                        <div id="rejectionReasonSection" class="hidden">
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Rejection Reason
                                <span class="text-red-500">*</span>
                                <span class="text-sm font-normal text-gray-600">(required for rejection)</span>
                            </label>
                            <textarea name="rejection_reason"
                                      id="rejectionReason"
                                      class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                                      rows="3"
                                      placeholder="Please provide a detailed reason for rejection..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    onclick="submitReview('reject')"
                                    class="flex items-center justify-center px-4 py-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject
                            </button>
                            <button type="button"
                                    onclick="submitReview('approve')"
                                    class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all font-medium shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approve
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Document Already Reviewed</h3>
                        <p class="text-gray-600 mb-4">This document has been {{ $document->status }} and cannot be modified.</p>
                        <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Review Completed
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Desktop Layout: Enhanced Side-by-Side -->
        <div class="hidden lg:grid lg:grid-cols-12 gap-8">
            <!-- Document Preview - Takes 8 columns -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Document Preview Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-2xl font-bold text-gray-900">Document Preview</h2>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium bg-gray-100 text-gray-800">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                {{ strtoupper($extension ?: 'FILE') }}
                            </span>
                            <div class="text-sm text-gray-600">
                                <span class="font-medium">File Size:</span> {{ $fileSize }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-8">
                        @if($isPdf)
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-lg">
                                    <iframe src="{{ $fileUrl }}#toolbar=0&navpanes=0&scrollbar=0&view=FitH"
                                            class="w-full h-[700px] border-0 rounded-xl"
                                            loading="lazy"
                                            title="PDF Document Preview">
                                    </iframe>
                                </div>
                                <div class="flex items-center justify-center space-x-4">
                                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors font-medium shadow-sm">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        Open in New Tab
                                    </a>
                                    <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                       class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl transition-colors font-medium shadow-sm">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @elseif($isImage)
                            <div class="space-y-6">
                                <div class="bg-white rounded-xl border-2 border-gray-200 p-6">
                                    <img src="{{ $fileUrl }}"
                                         alt="Document Preview"
                                         class="max-w-full h-auto rounded-xl shadow-lg mx-auto"
                                         style="max-height: 700px;"
                                         loading="lazy"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTEyIDE4SDlhMSAxIDAgMCAxLTEtMVY1YTEgMSAwIDAxMS0xaDQuNTg2YTEgMSAwIDAuNzA3LjI5M2w1LjQxNCA1LjQxNGEhMSAwIDAuMjg2LjI5N1YxOWExIDEgMCAwMS0xIDF6IiBzdHJva2U9IiM5Q0E0QUYiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo='; this.alt='Preview unavailable';">
                                </div>
                                <div class="flex items-center justify-center space-x-4">
                                    <button onclick="zoomImage(this.previousElementSibling.querySelector('img'), 1.2)"
                                            class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl transition-colors font-medium">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                        </svg>
                                        Zoom In
                                    </button>
                                    <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                       class="inline-flex items-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl transition-colors font-medium shadow-sm">
                                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Document Preview Unavailable</h3>
                                <p class="text-sm text-gray-600 mb-4">This file type cannot be previewed inline. Please download to view the document.</p>
                                <div class="flex items-center justify-center space-x-2 mb-4">
                                    <span class="text-sm text-gray-500">File Size: {{ $fileSize }}</span>
                                </div>
                                <a href="{{ $fileUrl }}&download=1" rel="noopener noreferrer"
                                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Document
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Document Verification Checklist -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-8">Verification Checklist</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is authentic</span>
                            </div>
                            <span class="text-xs text-gray-500">Auto-verified</span>
                        </div>
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is legible</span>
                            </div>
                            <span class="text-xs text-gray-500">Visual check</span>
                        </div>
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Information matches user profile</span>
                            </div>
                            <span class="text-xs text-gray-500">Cross-reference</span>
                        </div>
                        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">Document is not expired</span>
                            </div>
                            <span class="text-xs text-gray-500">Date validation</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop Sidebar - Takes 4 columns -->
            <div class="lg:col-span-4 space-y-6">
                <!-- User Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">User Information</h2>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                {{ substr($document->user->first_name, 0, 1) }}{{ substr($document->user->last_name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $document->user->full_name }}</h3>
                                <p class="text-gray-600">{{ $document->user->email }}</p>
                                <p class="text-xs text-gray-500 mt-1">User ID: {{ $document->user->id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Phone</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->phone_number ?? 'Not provided' }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Registration</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Last Login</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->updated_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($document->user->status->value === 'approved') bg-green-100 text-green-800
                                        @elseif($document->user->status->value === 'rejected') bg-red-100 text-red-800
                                        @elseif($document->user->status->value === 'review') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $document->user->status->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Document Details</h2>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Document ID</span>
                                <span class="text-sm font-mono font-semibold text-gray-900">{{ $document->id }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Type</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($document->document_type instanceof \App\Enums\Auth\DocumentType)
                                        {{ $document->document_type->label() }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Uploaded</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">File Size</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $fileSize }}</span>
                            </div>
                        </div>
                        @if($document->reviewed_at)
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Reviewed</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->reviewed_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        @endif
                        @if($document->status === 'rejected' && $document->rejection_reason)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Rejection Reason</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $document->rejection_reason }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Review Actions -->
                @if($document->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Review Actions</h2>

                    <form id="reviewForm" class="space-y-6">
                        @csrf

                        <!-- Review Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Review Notes
                                <span class="text-sm font-normal text-gray-600">(optional - for internal records)</span>
                            </label>
                            <textarea name="review_notes"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      rows="3"
                                      placeholder="Add any notes about your review decision..."></textarea>
                        </div>

                        <!-- Rejection Reason (conditionally shown) -->
                        <div id="rejectionReasonSection" class="hidden">
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Rejection Reason
                                <span class="text-red-500">*</span>
                                <span class="text-sm font-normal text-gray-600">(required for rejection)</span>
                            </label>
                            <textarea name="rejection_reason"
                                      id="rejectionReason"
                                      class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                                      rows="3"
                                      placeholder="Please provide a detailed reason for rejection..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    onclick="submitReview('reject')"
                                    class="flex items-center justify-center px-4 py-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject
                            </button>
                            <button type="button"
                                    onclick="submitReview('approve')"
                                    class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all font-medium shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approve
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Document Already Reviewed</h3>
                        <p class="text-gray-600 mb-4">This document has been {{ $document->status }} and cannot be modified.</p>
                        <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Review Completed
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
                <!-- User Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">User Information</h2>

                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                {{ substr($document->user->first_name, 0, 1) }}{{ substr($document->user->last_name, 0, 1) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $document->user->full_name }}</h3>
                                <p class="text-gray-600">{{ $document->user->email }}</p>
                                <p class="text-xs text-gray-500 mt-1">User ID: {{ $document->user->id }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-200">
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Phone</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->phone_number ?? 'Not provided' }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Registration</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->created_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Last Login</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $document->user->updated_at->format('M j, Y') }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-600">Status</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($document->user->status->value === 'approved') bg-green-100 text-green-800
                                        @elseif($document->user->status->value === 'rejected') bg-red-100 text-red-800
                                        @elseif($document->user->status->value === 'review') bg-blue-100 text-blue-800
                                        @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ $document->user->status->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Details -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Document Details</h2>

                    <div class="space-y-4">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Document ID</span>
                                <span class="text-sm font-mono font-semibold text-gray-900">{{ $document->id }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Type</span>
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($document->document_type instanceof \App\Enums\Auth\DocumentType)
                                        {{ $document->document_type->label() }}
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Uploaded</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->created_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">File Size</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $fileSize }}</span>
                            </div>
                        </div>
                        @if($document->reviewed_at)
                        <div class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600">Reviewed</span>
                                <span class="text-sm font-semibold text-gray-900">{{ $document->reviewed_at->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        @endif
                        @if($document->status === 'rejected' && $document->rejection_reason)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <div class="flex items-start space-x-2">
                                <svg class="w-4 h-4 text-red-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Rejection Reason</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $document->rejection_reason }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Review Actions -->
                @if($document->status === 'pending')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Review Actions</h2>

                    <form id="reviewForm" class="space-y-6">
                        @csrf

                        <!-- Review Notes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Review Notes
                                <span class="text-sm font-normal text-gray-600">(optional - for internal records)</span>
                            </label>
                            <textarea name="review_notes"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      rows="3"
                                      placeholder="Add any notes about your review decision..."></textarea>
                        </div>

                        <!-- Rejection Reason (conditionally shown) -->
                        <div id="rejectionReasonSection" class="hidden">
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Rejection Reason
                                <span class="text-red-500">*</span>
                                <span class="text-sm font-normal text-gray-600">(required for rejection)</span>
                            </label>
                            <textarea name="rejection_reason"
                                      id="rejectionReason"
                                      class="w-full px-3 py-2 border border-red-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none"
                                      rows="3"
                                      placeholder="Please provide a detailed reason for rejection..."></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button type="button"
                                    onclick="submitReview('reject')"
                                    class="flex items-center justify-center px-4 py-3 border-2 border-red-300 text-red-700 rounded-lg hover:bg-red-50 hover:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reject
                            </button>
                            <button type="button"
                                    onclick="submitReview('approve')"
                                    class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all font-medium shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approve
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <div class="text-center">
                        <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Document Already Reviewed</h3>
                        <p class="text-gray-600 mb-4">This document has been {{ $document->status }} and cannot be modified.</p>
                        <div class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Review Completed
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function submitReview(action) {
    const form = document.getElementById('reviewForm');
    const formData = new FormData(form);

    // Set the action URL based on approve/reject
    const actionUrl = action === 'approve'
        ? '{{ route("admin.kyc.approve", $document) }}'
        : '{{ route("admin.kyc.reject", $document) }}';

    // Show rejection reason field for reject action
    if (action === 'reject') {
        document.getElementById('rejectionReasonSection').classList.remove('hidden');
        const reason = formData.get('rejection_reason');
        if (!reason || reason.trim() === '') {
            alert('Please provide a rejection reason.');
            document.getElementById('rejectionReason').focus();
            return;
        }
    } else {
        document.getElementById('rejectionReasonSection').classList.add('hidden');
    }

    // Submit the form
    form.action = actionUrl;
    form.method = 'POST';
    form.submit();
}

function zoomImage(img, scale) {
    const currentScale = img.style.transform ? parseFloat(img.style.transform.replace('scale(', '').replace(')', '')) : 1;
    const newScale = currentScale * scale;
    img.style.transform = `scale(${newScale})`;
    img.style.transformOrigin = 'center center';
    img.style.transition = 'transform 0.3s ease';
}
</script>
@endsection