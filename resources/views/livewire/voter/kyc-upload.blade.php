<div class="space-y-6" role="main" aria-labelledby="kyc-heading">
    @php
        $user = Auth::user();
        $kycStatus = $user->getKycStatus();
        $canUpload = $user->canUploadKycDocuments();
        $remainingAttempts = $user->getRemainingResubmissionAttempts();
        $hasExceededLimit = $user->hasExceededResubmissionLimit();
        $hasPendingDocument = $user->hasPendingKycDocument();
    @endphp


    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-4 sm:p-6 lg:p-8 text-white">
        <div class="flex flex-col space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between space-y-4 sm:space-y-0">
                <div class="flex-1 min-w-0">
                    <h1 id="kyc-heading" class="text-xl sm:text-2xl lg:text-3xl font-bold leading-tight">Identity Verification</h1>
                    <!--p class="text-blue-100 mt-1 text-sm sm:text-base leading-relaxed">
                      {{ $kycStatus['subtext'] }}
                    </p-->
                </div>

                @if($canUpload)
                    <div class="flex-shrink-0 w-full sm:w-auto">
                        <button wire:click="openUploadModal"
                                class="w-full sm:w-auto px-4 py-3 sm:px-6 sm:py-3 bg-white text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600 min-h-[44px] font-semibold shadow-md text-sm sm:text-base"
                                aria-describedby="upload-help">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="whitespace-nowrap leading-none">Upload Document</span>
                        </button>
                        <div id="upload-help" class="sr-only">Open upload dialog to add identification documents</div>
                    </div>
                @else
                    <div class="flex-shrink-0 w-full sm:w-auto">
                        @if($kycStatus['status'] === 'approved')
                            <div class="flex items-center justify-center sm:justify-start px-3 py-2 sm:px-4 sm:py-3 bg-green-500 text-white rounded-lg shadow-md w-full sm:w-auto" role="status" aria-live="polite">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span class="font-semibold text-sm sm:text-base whitespace-nowrap">Verified</span>
                            </div>
                        @elseif($kycStatus['status'] === 'pending')
                            <div class="flex items-center justify-center sm:justify-start px-3 py-2 sm:px-4 sm:py-3 bg-yellow-500 text-white rounded-lg shadow-md w-full sm:w-auto" role="status" aria-live="polite">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-semibold text-sm sm:text-base whitespace-nowrap">Under Review</span>
                            </div>
                        @else
                            @if($hasPendingDocument)
                                <div class="flex items-center justify-center sm:justify-start px-3 py-2 sm:px-4 sm:py-3 bg-blue-500 text-white rounded-lg shadow-md cursor-not-allowed w-full sm:w-auto" role="status" title="Document already submitted and under review">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    <span class="font-semibold text-sm sm:text-base whitespace-nowrap">Under Review</span>
                                </div>
                            @elseif($hasExceededLimit)
                                <div class="flex items-center justify-center sm:justify-start px-3 py-2 sm:px-4 sm:py-3 bg-gray-500 text-white rounded-lg shadow-md cursor-not-allowed w-full sm:w-auto" role="status" title="Maximum resubmission attempts exceeded">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span class="font-semibold text-sm sm:text-base whitespace-nowrap">Limit Exceeded</span>
                                </div>
                            @else
                                <div class="flex items-center justify-center sm:justify-start px-3 py-2 sm:px-4 sm:py-3 bg-red-500 text-white rounded-lg shadow-md w-full sm:w-auto" role="status" title="{{ $remainingAttempts }} resubmission attempts remaining">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                    <span class="font-semibold text-sm sm:text-base whitespace-nowrap">Resubmit Required ({{ $remainingAttempts }} left)</span>
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </header>

    <!-- Documents List -->
    <section class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden" aria-labelledby="documents-heading">
        <header class="px-4 sm:px-6 lg:px-8 py-4 sm:py-5 bg-gray-50 border-b border-gray-200">
            <h2 id="documents-heading" class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900">Your Documents</h2>
            <p class="text-sm text-gray-600 mt-1">Track the status and review progress of your uploaded documents</p>
        </header>

        <div class="p-4 sm:p-6 lg:p-8">
            @if(count($documents) > 0)
                <div class="space-y-3 sm:space-y-4" role="list">
                    @foreach($documents as $document)
                        <article class="border border-gray-200 rounded-lg p-4 sm:p-5 lg:p-6 hover:shadow-md transition-shadow duration-200" role="listitem">
                            <div class="flex flex-col space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between space-y-3 sm:space-y-0">
                                    <div class="flex items-start space-x-3 sm:space-x-4 flex-1 min-w-0">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center shadow-sm" aria-hidden="true">
                                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 truncate">{{ $document->getDocumentTypeLabel() }}</h3>
                                            <div class="mt-1 space-y-1">
                                                <p class="text-xs sm:text-sm text-gray-600">
                                                    <span class="font-medium">Uploaded:</span> <span class="sm:hidden"><br></span>{{ $document->created_at->format('M j, Y \a\t g:i A') }}
                                                </p>
                                                @if($document->reviewed_at)
                                                    <p class="text-xs sm:text-sm text-gray-600">
                                                        <span class="font-medium">
                                                            @if($document->status === 'approved')
                                                                Approved:
                                                            @else
                                                                Reviewed:
                                                            @endif
                                                        </span>
                                                        <span class="sm:hidden"><br></span>{{ $document->reviewed_at->format('M j, Y \a\t g:i A') }}
                                                        @if($document->status === 'approved' && $document->reviewer)
                                                            <span class="sm:hidden"><br></span>by {{ $document->reviewer->full_name }}
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 flex-shrink-0">
                                        <!-- Status Badge -->
                                        <div class="flex items-center">
                                            @if($document->status === 'approved')
                                                <span class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-green-100 text-green-800 shadow-sm relative group"
                                                      role="status"
                                                      x-data="{ tooltip: false }"
                                                      @mouseenter="tooltip = true"
                                                      @mouseleave="tooltip = false">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="hidden sm:inline">Approved</span>
                                                    <span class="sm:hidden">Approved</span>
                                                    <!-- Tooltip -->
                                                    <div x-show="tooltip" x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded shadow-lg z-10 whitespace-nowrap">
                                                        Document has been verified and approved
                                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                                    </div>
                                                </span>
                                            @elseif($document->status === 'pending')
                                                <span class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-yellow-100 text-yellow-800 shadow-sm relative group"
                                                      role="status"
                                                      x-data="{ tooltip: false }"
                                                      @mouseenter="tooltip = true"
                                                      @mouseleave="tooltip = false">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                    <span class="hidden sm:inline">Under Review</span>
                                                    <span class="sm:hidden">Under Review</span>
                                                    <!-- Tooltip -->
                                                    <div x-show="tooltip" x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded shadow-lg z-10 whitespace-nowrap">
                                                        Document is being reviewed by our team
                                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                                    </div>
                                                </span>
                                            @elseif($document->status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-medium bg-red-100 text-red-800 shadow-sm relative group"
                                                      role="status"
                                                      x-data="{ tooltip: false }"
                                                      @mouseenter="tooltip = true"
                                                      @mouseleave="tooltip = false">
                                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="hidden sm:inline">Rejected</span>
                                                    <span class="sm:hidden">Rejected</span>
                                                    <!-- Tooltip -->
                                                    <div x-show="tooltip" x-transition class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded shadow-lg z-10 whitespace-nowrap">
                                                        Document was rejected - check rejection reason below
                                                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                                    </div>
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Action Button -->
                                        @if($document->status === 'approved')
                                            <a href="{{ route('voter.document.view', $document->id) }}"
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-2 sm:px-4 sm:py-2 border border-blue-300 text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg text-xs sm:text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 whitespace-nowrap">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span class="hidden sm:inline">View Document</span>
                                                <span class="sm:hidden">View Document</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                @if($document->status === 'rejected' && $document->rejection_reason)
                                    <div class="border-t border-gray-100 pt-4">
                                        <div class="p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg" role="alert">
                                            <div class="flex items-start">
                                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-400 mt-0.5 mr-2 sm:mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-xs sm:text-sm font-medium text-red-800">Rejection Reason</h4>
                                                    <p class="text-xs sm:text-sm text-red-700 mt-1">{{ $document->rejection_reason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 sm:py-16 lg:py-20" role="status">
                    <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 lg:w-12 lg:h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base sm:text-lg lg:text-xl font-medium text-gray-900 mb-2">No documents uploaded yet</h3>
                    <p class="text-sm sm:text-base text-gray-600 mb-4 sm:mb-6 max-w-sm mx-auto px-4">Get started by uploading your first identification document. We'll review it promptly to verify your identity.</p>
                    @if($canUpload)
                        <button wire:click="openUploadModal"
                                class="inline-flex items-center px-4 py-2 sm:px-6 sm:py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm sm:text-base">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0 self-center" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="whitespace-nowrap leading-none">Upload Your First Document</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <!-- Upload Modal -->
    @if($showUploadModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 p-2 sm:p-4"
              role="dialog"
              aria-modal="true"
              aria-labelledby="upload-modal-title"
              aria-describedby="upload-modal-description"
              id="upload-modal"
              x-data="{ show: true }"
              x-show="show"
              x-transition:enter="transition ease-out duration-300"
              x-transition:enter-start="opacity-0"
              x-transition:enter-end="opacity-100"
              x-transition:leave="transition ease-in duration-200"
              x-transition:leave-start="opacity-100"
              x-transition:leave-end="opacity-0">
            <div class="flex items-start sm:items-center justify-center min-h-screen py-2 sm:py-4">
                <div class="relative mx-auto w-full max-w-sm sm:max-w-md lg:max-w-lg bg-white rounded-lg sm:rounded-xl lg:rounded-2xl shadow-2xl transform transition-all max-h-[90vh] overflow-y-auto"
                      x-transition:enter="transition ease-out duration-300"
                      x-transition:enter-start="opacity-0 scale-95"
                      x-transition:enter-end="opacity-100 scale-100"
                      x-transition:leave="transition ease-in duration-200"
                      x-transition:leave-start="opacity-100 scale-100"
                      x-transition:leave-end="opacity-0 scale-95">

                    <!-- Header -->
                    <div class="flex items-center justify-between p-3 sm:p-4 lg:p-6 border-b border-gray-200">
                        <div class="flex-1 min-w-0 pr-2 sm:pr-4">
                            <h3 id="upload-modal-title" class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900">Upload Identity Document</h3>
                            <p id="upload-modal-description" class="text-xs sm:text-sm text-gray-600 mt-1">Submit your document for verification</p>
                        </div>
                        <button wire:click="closeUploadModal"
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-1 sm:p-1.5 lg:p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 flex-shrink-0"
                                aria-label="Close upload dialog">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-3 sm:p-4 lg:p-6 pb-4 sm:pb-6 lg:pb-8">
                        {{-- Progress Indicator --}}
                        @if($uploadProgress > 0 && $uploadProgress < 100)
                            <div class="mb-4 sm:mb-6" role="progressbar" aria-valuenow="{{ $uploadProgress }}" aria-valuemin="0" aria-valuemax="100" aria-label="Upload progress">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Uploading...</span>
                                    <span class="text-sm text-gray-600">{{ $uploadProgress }}%</span>
                                </div>
                                <div class="bg-gray-200 rounded-full h-2 sm:h-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 sm:h-3 rounded-full transition-all duration-500 ease-out" style="width: {{ $uploadProgress }}%"></div>
                                </div>
                            </div>
                        @endif

                        {{-- Status Messages --}}
                        @if($errors->any())
                            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg" role="alert" aria-live="assertive">
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-400 mt-0.5 mr-2 sm:mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-red-800">Please correct the following errors:</h4>
                                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form wire:submit="uploadDocument" role="form" aria-labelledby="upload-modal-title" class="space-y-4 sm:space-y-6">
                            <!-- Document Type Selection -->
                            <div>
                                <label for="document-type" class="block text-sm font-semibold text-gray-900 mb-2 sm:mb-3">
                                    Document Type <span class="text-red-500">*</span>
                                </label>
                                <select id="document-type" wire:model="documentType"
                                        class="w-full px-3 py-2 sm:px-4 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white min-h-[40px] sm:min-h-[44px] transition-colors duration-200 text-sm sm:text-base"
                                        aria-describedby="document-type-help document-type-error">
                                    <option value="">Select a document type...</option>
                                    @foreach($documentTypes as $type)
                                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                <div id="document-type-help" class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">
                                    Choose one type of identification document
                                </div>
                                @error('documentType')
                                    <p id="document-type-error" class="mt-1 sm:mt-2 text-xs sm:text-sm text-red-600 flex items-center" role="alert">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 000 16zM7 9a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2H10z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- File Upload -->
                            <div>
                                <label for="document-file" class="block text-sm font-semibold text-gray-900 mb-2 sm:mb-3">
                                    Document File <span class="text-red-500">*</span>
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-3 sm:p-4 lg:p-6 transition-all duration-200 hover:border-blue-400 focus-within:border-blue-500"
                                      id="drop-zone"
                                      x-data="{ isDragOver: false }"
                                      :class="{ 'border-blue-500 bg-blue-50': isDragOver }"
                                      @dragover.prevent="isDragOver = true"
                                      @dragleave.prevent="isDragOver = false"
                                      @drop.prevent="isDragOver = false; handleDrop($event)">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 sm:h-10 sm:w-10 lg:h-12 lg:w-12 text-gray-400 mb-2 sm:mb-3 lg:mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <div class="flex flex-col sm:flex-row text-xs sm:text-sm text-gray-600 justify-center items-center space-y-1 sm:space-y-0 sm:space-x-1">
                                            <label for="document-file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1 transition-colors">
                                                <span>Choose file</span>
                                                <input id="document-file" type="file" wire:model="uploadedFile" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" @change="handleFileSelect($event)">
                                            </label>
                                            <span class="hidden sm:inline text-gray-400">or</span>
                                            <span class="sm:hidden text-gray-400">/</span>
                                            <span class="text-center">drag and drop</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 sm:mt-2 lg:mt-3">JPG, PNG, PDF up to 5MB</p>

                                        <!-- File Preview -->
                                        @if($uploadedFile)
                                            <div class="mt-3 sm:mt-4 p-2 sm:p-3 bg-green-50 border border-green-200 rounded-lg">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center flex-1 min-w-0">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="text-xs sm:text-sm font-medium text-green-800 truncate">{{ $uploadedFile->getClientOriginalName() }}</span>
                                                    </div>
                                                    <button type="button" wire:click="resetForm" class="text-green-600 hover:text-green-800 ml-2 flex-shrink-0">
                                                        <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Drag Over Indicator -->
                                        <div x-show="isDragOver" class="absolute inset-0 bg-blue-500 bg-opacity-10 rounded-lg flex items-center justify-center">
                                            <div class="text-blue-600 font-medium text-sm sm:text-base">Drop file here</div>
                                        </div>
                                    </div>
                                </div>
                                @error('uploadedFile')
                                    <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-red-600 flex items-center" role="alert">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 000 16zM7 9a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2H10z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-3 sm:pt-4 lg:pt-6 border-t border-gray-200">
                                <button type="button" wire:click="closeUploadModal"
                                        class="w-full sm:w-auto px-3 py-2 sm:px-4 sm:py-3 lg:px-6 lg:py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors duration-200 font-medium min-h-[40px] sm:min-h-[44px] text-sm sm:text-base">
                                    Cancel
                                </button>
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="w-full sm:w-auto px-3 py-2 sm:px-4 sm:py-3 lg:px-6 lg:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 font-medium min-h-[40px] sm:min-h-[44px] shadow-md text-sm sm:text-base"
                                        aria-describedby="upload-help">
                                    <span wire:loading.remove>
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 lg:w-5 lg:h-5 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <span class="hidden sm:inline">Upload Document</span>
                                        <span class="sm:hidden">Upload</span>
                                    </span>
                                    <span wire:loading class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-1 sm:mr-2 lg:mr-3 h-3 w-3 sm:h-4 sm:w-4 lg:h-5 lg:w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Uploading...</span>
                                    </span>
                                </button>
                                <div id="upload-help" class="sr-only">Submit the document for verification</div>
                            </div>
                        </form>

                        <!-- Help Section -->
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Need help with document upload?</p>
                                <button class="inline-flex items-center text-sm text-blue-600 hover:text-blue-500 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 rounded px-2 py-1"
                                        onclick="alert('📋 Accepted Documents:\n• National ID Card\n• Passport\n• Driver\'s License\n\n📏 Requirements:\n• Clear, readable text\n• All corners visible\n• No glare or shadows\n• Under 5MB in size\n\n📞 Support: support@echara.org')"
                                        aria-label="Get help with document upload">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    View Guidelines
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        // Listen for upload success event
        document.addEventListener('livewire:init', () => {
            Livewire.on('upload-success', () => {
                // Force close modal
                const modal = document.getElementById('upload-modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });
        
        function handleDrop(event) {
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid file type (JPG, PNG, or PDF).');
                    return;
                }
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must not exceed 5MB.');
                    return;
                }
                // Set the file to the input
                const input = document.getElementById('document-file');
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                // Trigger change event
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Basic client-side validation
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid file type (JPG, PNG, or PDF).');
                    event.target.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must not exceed 5MB.');
                    event.target.value = '';
                    return;
                }
            }
        }

        function clearFile() {
            // This function is now handled by wire:click="resetForm"
        }
    </script>
</div>