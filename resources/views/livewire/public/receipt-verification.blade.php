<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-600 mb-2 flex items-center justify-center gap-2">
                Vote Verification
                @if($verificationResult)
                    @if($verificationResult['valid'])
                        <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg class="h-8 w-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                @else
                    <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </h1>
            <p class="text-lg text-gray-600">Verify that your vote was recorded and included in the election results</p>
        </div>

        <!-- Verification Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-8">
            <div class="mb-6">
                <label for="receiptHash" class="block text-sm font-medium text-gray-700 mb-2">
                    Receipt Hash
                </label>
                <div class="relative">
                    <input type="text"
                           wire:model.live.debounce.500ms="receiptHash"
                           id="receiptHash"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                           placeholder="Enter your receipt hash (e.g., a1b2c3d4...)"
                           autocomplete="off">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        @if($receiptHash)
                            <button wire:click="clearResults" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
                @error('receiptHash') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-center">
                <button wire:click="verifyReceipt"
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                    <span wire:loading.remove>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span wire:loading>
                        <svg class="animate-spin h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </span>
                    <span wire:loading.remove>Verify Your Vote</span>
                    <span wire:loading>Verifying...</span>
                </button>
            </div>
        </div>

        <!-- Error Message -->
        @if($errorMessage)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800">{{ $errorMessage }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Verification Result -->
        @if($verificationResult)
            <div class="bg-white rounded-xl shadow-lg border-2 {{ $verificationResult['valid'] ? 'border-blue-200 bg-blue-50/30' : 'border-red-200 bg-red-50/30' }} overflow-hidden">
                <!-- Status Header -->
                <div class="px-4 sm:px-6 lg:px-8 py-6 border-b {{ $verificationResult['valid'] ? 'border-blue-200 bg-blue-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex flex-col sm:flex-row items-center text-center sm:text-left">
                        <div class="flex-shrink-0 mb-4 sm:mb-0">
                            @if($verificationResult['valid'])
                                <div class="mx-auto sm:mx-0 w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @else
                                <div class="mx-auto sm:mx-0 w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="sm:ml-6">
                            <h3 class="text-xl sm:text-2xl font-bold {{ $verificationResult['valid'] ? 'text-blue-800' : 'text-red-800' }}">
                                {{ $verificationResult['valid'] ? '✓ Vote Verified Successfully' : '✗ Vote Verification Failed' }}
                            </h3>
                            <p class="text-sm sm:text-base {{ $verificationResult['valid'] ? 'text-blue-700' : 'text-red-700' }} mt-2">
                                {{ $verificationResult['valid'] ? 'Your vote has been confirmed and is included in the election results.' : 'This receipt is not part of the vote chain for this election.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Details -->
                @if($verificationResult['valid'])
                    <div class="px-4 sm:px-6 lg:px-8 py-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <dt class="text-xs font-medium text-blue-600 uppercase tracking-wide">Election</dt>
                                <dd class="mt-2 text-sm sm:text-base font-semibold text-gray-900">{{ $verificationResult['election_title'] ?? 'Unknown' }}</dd>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <dt class="text-xs font-medium text-blue-600 uppercase tracking-wide">Position</dt>
                                <dd class="mt-2 text-sm sm:text-base font-semibold text-gray-900">{{ $verificationResult['position_title'] ?? 'Unknown' }}</dd>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <dt class="text-xs font-medium text-blue-600 uppercase tracking-wide">Vote Cast At</dt>
                                <dd class="mt-2 text-sm sm:text-base font-semibold text-gray-900">
                                    {{ $verificationResult['cast_at'] ? \Carbon\Carbon::parse($verificationResult['cast_at'])->format('M j, Y \a\t g:i A') : 'Unknown' }}
                                </dd>
                            </div>
                            <div class="bg-white rounded-lg p-4 border border-blue-200">
                                <dt class="text-xs font-medium text-blue-600 uppercase tracking-wide">Chain Position</dt>
                                <dd class="mt-2 text-sm sm:text-base font-semibold text-gray-900">#{{ $verificationResult['chain_position'] ?? 'Unknown' }}</dd>
                            </div>
                            <div class="sm:col-span-2 bg-white rounded-lg p-4 border border-blue-200">
                                <dt class="text-xs font-medium text-blue-600 uppercase tracking-wide">Vote Hash</dt>
                                <dd class="mt-2 text-xs sm:text-sm font-mono text-gray-900 bg-gray-50 px-3 py-2 rounded break-all">
                                    {{ $verificationResult['vote_hash'] ?? 'Unknown' }}
                                </dd>
                            </div>
                        </div>

                        <!-- Security Notice -->
                        <div class="mt-6 bg-blue-100 border-l-4 border-blue-500 rounded-r-lg p-4">
                            <div class="flex flex-col sm:flex-row">
                                <div class="flex-shrink-0 mb-3 sm:mb-0">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                </div>
                                <div class="sm:ml-4">
                                    <h4 class="text-sm sm:text-base font-semibold text-blue-800">✓ Cryptographically Verified</h4>
                                    <p class="text-sm text-blue-700 mt-1">
                                        Your vote has been cryptographically verified and is permanently recorded in the election blockchain. This ensures your vote was counted and cannot be altered.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-4 sm:px-6 lg:px-8 py-6">
                        <div class="bg-red-100 border-l-4 border-red-500 rounded-r-lg p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row">
                                <div class="flex-shrink-0 mb-4 sm:mb-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                                <div class="sm:ml-4">
                                    <h4 class="text-base sm:text-lg font-semibold text-red-800 mb-3">Receipt Not Found</h4>
                                    <p class="text-sm sm:text-base text-red-700 mb-4">
                                        This reciept is not part of the vote chain for this election, pls make sure the hash is correct or contact Eleco.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Information Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="text-center mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-2">How It Works</h2>
                <p class="text-gray-600">Our cryptographic system ensures every vote is verifiable and tamper-proof</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="mx-auto h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Cryptographic Security</h3>
                    <p class="text-sm text-gray-600">Each vote is protected by multiple layers of cryptographic hashing and digital signatures.</p>
                </div>

                <div class="text-center">
                    <div class="mx-auto h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Chain of Trust</h3>
                    <p class="text-sm text-gray-600">Votes are linked in an immutable chain, ensuring no votes can be added, removed, or altered.</p>
                </div>

                <div class="text-center">
                    <div class="mx-auto h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Public Verification</h3>
                    <p class="text-sm text-gray-600">Anyone can verify that their vote was counted without revealing how they voted.</p>
                </div>
            </div>
        </div>
    </div>
</div>